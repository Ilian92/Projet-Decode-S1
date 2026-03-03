<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Subscription;
use App\Entity\SubscriptionOffer;
use App\Repository\SubscriptionRepository;
use App\Repository\SubscriptionOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;

#[Route('/abonnement', name: 'app_subscription_')]
class SubscriptionController extends AbstractController
{
    public function __construct(
        private SubscriptionOfferRepository $subscriptionOfferRepository,
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        $offers = $this->subscriptionOfferRepository->findBy([], ['monthlyPrice' => 'ASC']);

        return $this->render('subscription/index.html.twig', [
            'offers' => $offers,
        ]);
    }

    #[Route('/checkout/{id}', name: 'checkout', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function checkout(SubscriptionOffer $offer, Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer) {
            $this->addFlash('error', 'Vous devez être connecté pour souscrire à un abonnement.');

            return $this->redirectToRoute('app_login');
        }

        $subscription = $this->subscriptionRepository->findBy(['customer' => $user], ['id' => 'DESC']);
        if (!empty($subscription) && $subscription[0]->getStatus() === 'active') {
            $this->addFlash('error', 'Vous avez déjà un abonnement actif.');

            return $this->redirectToRoute('app_profile');
        }

        if ($user->getAddress() === null) {
            $this->addFlash('error', 'Veuillez renseigner une adresse de livraison dans votre profil avant de souscrire.');

            return $this->redirectToRoute('app_profile');
        }

        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$secretKey) {
            $this->addFlash('error', 'Le paiement est temporairement indisponible. Veuillez réessayer plus tard.');

            return $this->redirectToRoute('app_subscription_index');
        }

        Stripe::setApiKey($secretKey);

        $priceInCents = $offer->getMonthlyPrice();
        $lineItems = [
            [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $priceInCents,
                    'product_data' => [
                        'name' => $offer->getOfferName(),
                        'description' => $offer->getDescription(),
                        'metadata' => [
                            'subscription_offer_id' => (string) $offer->getId(),
                        ],
                    ],
                    'recurring' => [
                        'interval' => 'month',
                    ],
                ],
                'quantity' => 1,
            ],
        ];

        $session = CheckoutSession::create([
            'mode' => 'subscription',
            'line_items' => $lineItems,
            'success_url' => $this->generateUrl('app_subscription_success', [], UrlGeneratorInterface::ABSOLUTE_URL) . '?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => $this->generateUrl('app_subscription_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'customer_email' => $user->getEmail(),
            'client_reference_id' => (string) $user->getId(),
            'metadata' => [
                'subscription_offer_id' => (string) $offer->getId(),
                'customer_id' => (string) $user->getId(),
            ],
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/success', name: 'success', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function success(Request $request): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer) {
            $this->addFlash('error', 'Session invalide.');

            return $this->redirectToRoute('app_subscription_index');
        }

        $sessionId = $request->query->get('session_id');
        if ($sessionId === null || $sessionId === '') {
            $this->addFlash('error', 'Session de paiement invalide.');

            return $this->redirectToRoute('app_subscription_index');
        }

        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;
        if (!$secretKey) {
            $this->addFlash('error', 'Impossible de finaliser l\'abonnement. Réessayez plus tard.');

            return $this->redirectToRoute('app_subscription_index');
        }

        Stripe::setApiKey($secretKey);

        try {
            $session = CheckoutSession::retrieve($sessionId, [
                'expand' => ['subscription'],
            ]);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Session de paiement introuvable ou expirée.');

            return $this->redirectToRoute('app_subscription_index');
        }

        if ($session->mode !== 'subscription' || $session->subscription === null) {
            $this->addFlash('error', 'Cette session ne correspond pas à un abonnement.');

            return $this->redirectToRoute('app_subscription_index');
        }

        $offerId = (int) ($session->metadata['subscription_offer_id'] ?? 0);
        $offer = $this->subscriptionOfferRepository->find($offerId);
        if ($offer === null) {
            $this->addFlash('error', 'Offre d\'abonnement introuvable.');

            return $this->redirectToRoute('app_subscription_index');
        }

        $stripeSubscriptionId = null;
        if (is_object($session->subscription) && isset($session->subscription->id)) {
            $stripeSubscriptionId = $session->subscription->id;
        } elseif (is_string($session->subscription)) {
            $stripeSubscriptionId = $session->subscription;
        }

        $subscription = new Subscription();
        $subscription->setCustomer($user);
        $subscription->setSubscriptionOffer($offer);
        $subscription->setAddress($user->getAddress());
        $startDate = new \DateTime();
        $subscription->setStartDate($startDate);
        $expectedEnd = (clone $startDate)->modify('+' . $offer->getCommitmentMonths() . ' months');
        $subscription->setExpectedEndDate($expectedEnd);
        $nextPayment = (clone $startDate)->modify('+1 month');
        $subscription->setNextPaymentDate($nextPayment);
        if ($stripeSubscriptionId !== null) {
            $subscription->setStripeSubscriptionId($stripeSubscriptionId);
        }

        $this->entityManager->persist($subscription);
        $this->entityManager->flush();

        $this->addFlash('success', 'Merci ! Votre abonnement « ' . $offer->getOfferName() . ' » est actif. Vous le retrouverez dans votre espace profil.');

        return $this->redirectToRoute('app_profile');
    }
}
