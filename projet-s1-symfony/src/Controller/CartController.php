<?php

namespace App\Controller;

use App\Entity\Book;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/cart', name: 'cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private CartService $cartService
    ) {
    }

    /**
     * Afficher le panier
     */
    #[Route('', name: 'show')]
    public function show(): Response
    {
        $cartItems = $this->cartService->getCartWithDetails();
        $totalAmount = $this->cartService->getTotalAmount();

        return $this->render('cart/index.html.twig', [
            'cartItems' => $cartItems,
            'totalAmount' => $totalAmount,
        ]);
    }

    /**
     * Ajouter un livre au panier
     */
    #[Route('/add/{id}', name: 'add', methods: ['POST', 'GET'])]
    public function add(Book $book, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        if ($quantity < 1) {
            $quantity = 1;
        }

        $cookie = $this->cartService->add($book->getId(), $quantity);

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
    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(int $id, Request $request): Response
    {
        $quantity = (int) $request->request->get('quantity', 1);

        $cookie = $this->cartService->updateQuantity($id, $quantity);

        $this->addFlash('success', 'Le panier a été mis à jour.');

        $response = $this->redirectToRoute('cart_show');
        $response->headers->setCookie($cookie);

        return $response;
    }

    /**
     * Retirer un livre du panier
     */
    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    public function remove(int $id): Response
    {
        $cookie = $this->cartService->remove($id);

        $this->addFlash('success', 'Le livre a été retiré du panier.');

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
}
