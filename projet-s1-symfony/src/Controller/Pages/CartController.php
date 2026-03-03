<?php

namespace App\Controller\Pages;

use App\Entity\Customer;
use App\Service\CartService;
use App\Service\OrderService;
use Stripe\Checkout\Session as CheckoutSession;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService
    ) {
    }

    /**
     * Afficher le panier
     */
    #[Route('', name: 'show')]
    public function show(): Response
    {
        $cartItems = $this->cartService->getCartWithDetails();
        $subtotalAmount = $this->cartService->getArticleAmount();
        $shippingCost = $this->cartService->getShippingCost();
        $totalAmount = $this->cartService->getTotalAmount();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'subtotalAmount' => $subtotalAmount,
            'shippingCost' => $shippingCost,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Ajouter un livre au panier
     */
    #[Route('/add', name: 'add', methods: ['POST'])]
    public function add(Request $request): Response
    {
        $bookOlid = $request->request->getString('book_olid');
        $workOlid = $request->request->getString('work_olid');
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1) {
            $quantity = 1;
        }

        if ($bookOlid === '' || $workOlid === '') {
            $this->addFlash('error', 'Données du livre manquantes.');

            return $this->redirect($request->headers->get('referer', $this->generateUrl('cart_show')));
        }

        $cookie = $this->cartService->add($bookOlid, $workOlid, $quantity);

        $this->addFlash('success', 'Le livre a été ajouté au panier !');

        $referer = $request->headers->get('referer');
        $response = $referer
            ? $this->redirect($referer)
            : $this->redirectToRoute('cart_show');
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Mettre à jour la quantité d'un livre dans le panier
     */
    #[Route('/update', name: 'update', methods: ['POST'])]
    public function update(Request $request): Response
    {
        $bookOlid = $request->request->getString('book_olid');
        $quantity = (int) $request->request->get('quantity', 1);

        if ($bookOlid !== '') {
            $cookie = $this->cartService->updateQuantity($bookOlid, $quantity);
            $this->addFlash('success', 'Le panier a été mis à jour.');
        } else {
            $cookie = $this->cartService->createCartCookie();
        }

        $response = $this->redirectToRoute('cart_show');
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Retirer un livre du panier
     */
    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function remove(Request $request): Response
    {
        $bookOlid = $request->request->getString('book_olid');

        if ($bookOlid !== '') {
            $cookie = $this->cartService->remove($bookOlid);
            $this->addFlash('success', 'Le livre a été retiré du panier.');
        } else {
            $cookie = $this->cartService->createCartCookie();
        }

        $response = $this->redirectToRoute('cart_show');
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Vider le panier
     */
    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clear(): Response
    {
        $cookie = $this->cartService->clear();

        $this->addFlash('success', 'Le panier a été vidé.');

        $response = $this->redirectToRoute('cart_show');
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Rediriger vers Stripe Checkout pour le paiement du panier
     */
    #[Route('/checkout', name: 'checkout', methods: ['POST'])]
    public function checkout(): Response
    {
        $cartItems = $this->cartService->getCartWithDetails();
        $totalAmount = $this->cartService->getTotalAmount();

        if (empty($cartItems) || $totalAmount <= 0) {
            $this->addFlash('success', 'Votre panier est vide.');

            return $this->redirectToRoute('cart_show');
        }

        $secretKey = $_ENV['STRIPE_SECRET_KEY'] ?? null;

        if (!$secretKey) {
            $this->addFlash('success', 'Le paiement est temporairement indisponible. Veuillez réessayer plus tard.');

            return $this->redirectToRoute('cart_show');
        }

        Stripe::setApiKey($secretKey);

        $lineItems = [];

        foreach ($cartItems as $item) {
            $book = $item['book'];
            $work = $book->getWork();

            $lineItems[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => $book->getCurrentUnitPrice(),
                    'product_data' => [
                        'name' => $work->getTitle(),
                    ],
                ],
                'quantity' => $item['quantity'],
            ];
        }

        $lineItems[] = [
            'price_data' => [
                'currency' => 'eur',
                'unit_amount' => $this->cartService->getShippingCost(),
                'product_data' => [
                    'name' => 'Livraison',
                ],
            ],
            'quantity' => 1,
        ];

        $session = CheckoutSession::create([
            'mode' => 'payment',
            'line_items' => $lineItems,
            'success_url' => $this->generateUrl('cart_payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('cart_show', [], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($session->url, 303);
    }

    #[Route('/payment-success', name: 'payment_success', methods: ['GET'])]
    public function paymentSuccess(): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer) {
            $this->addFlash('error', 'Vous devez être connecté pour finaliser votre commande.');

            return $this->redirectToRoute('app_login');
        }

        $cartItems = $this->cartService->getCartWithDetails();
        $order = $this->orderService->create($user, $cartItems);
        if ($order === null) {
            if (empty($cartItems)) {
                $this->addFlash('success', 'Votre panier est vide ou a déjà été commandé.');
            } else {
                $this->addFlash('error', 'Impossible de créer la commande. Vérifiez que votre adresse de livraison est renseignée dans votre profil.');
            }

            return $this->redirectToRoute('cart_show');
        }

        $response = $this->redirectToRoute('app_profile');
        $response->headers->setCookie($this->cartService->clear());
        $this->addFlash('success', 'Merci pour votre achat ! Votre commande #' . $order->getId() . ' a été enregistrée.');

        return $response;
    }
}
