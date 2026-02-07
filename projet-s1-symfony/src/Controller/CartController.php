<?php

namespace App\Controller;

use App\Entity\Book;
use App\Service\CartService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

class CartController extends AbstractController
{
    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add(Book $book, CartService $cartService): Response
    {
        $cartService->add($book->getId());

        return $this->redirectToRoute('cart_show');
    }
}
