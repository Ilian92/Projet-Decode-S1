<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Service\StripeRefundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile/orders', name: 'app_order_')]
final class CustomerOrderController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Order $order): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer || $order->getCustomer() !== $user) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'customer' => $user,
        ]);
    }

    #[Route('/{id}/refund', name: 'refund', requirements: ['id' => '\d+'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function refund(Request $request, Order $order, StripeRefundService $stripeRefundService): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer || $order->getCustomer() !== $user) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        if (!$this->isCsrfTokenValid('order_refund' . $order->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStripePaymentIntentId() === null || $order->getStripePaymentIntentId() === '') {
            $this->addFlash('error', 'Cette commande ne peut pas être remboursée en ligne. Contactez le service client.');
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($order->getStatus() === OrderStatus::REFUNDED->value) {
            $this->addFlash('error', 'Cette commande a déjà été remboursée.');
            return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($stripeRefundService->refundOrder($order)) {
            $this->addFlash('success', 'Votre demande de remboursement a été traitée. Le montant sera recrédité sur votre moyen de paiement sous quelques jours.');
        } else {
            $this->addFlash('error', 'Le remboursement n\'a pas pu être effectué. Veuillez réessayer ou contacter le service client.');
        }

        return $this->redirectToRoute('app_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }
}

