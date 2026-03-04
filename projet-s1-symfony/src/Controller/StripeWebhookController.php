<?php

namespace App\Controller;

use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route('/stripe', name: 'app_stripe_')]
class StripeWebhookController extends AbstractController
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
        private string $webhookSecret
    ) {
    }

    #[Route('/webhook', name: 'webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        if ($this->webhookSecret === '') {
            return new Response('Webhook non configuré', Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $payload = $request->getContent();
        $signature = $request->headers->get('Stripe-Signature', '');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                $this->webhookSecret
            );
        } catch (\UnexpectedValueException $e) {
            return new Response('Payload invalide', Response::HTTP_BAD_REQUEST);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Signature invalide', Response::HTTP_BAD_REQUEST);
        }

        if ($event->type === 'payment_intent.succeeded') {
            $paymentIntent = $event->data->object;
            $paymentIntentId = $paymentIntent->id ?? null;
            if ($paymentIntentId !== null) {
                $order = $this->orderRepository->findOneByStripePaymentIntentId($paymentIntentId);
                if ($order !== null && $order->getStatus() === OrderStatus::PAYMENT_PENDING->value) {
                    if ($order->isFulfillable()) {
                        $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);
                        $this->entityManager->flush();
                    } else {
                        $order->setStatus(OrderStatus::PENDING_RESTOCK->value);
                        $this->entityManager->flush();
                    }
                }
            }
        }

        return new Response('', Response::HTTP_OK);
    }
}
