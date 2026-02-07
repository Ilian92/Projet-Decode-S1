<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private $session;
    private const CART_KEY = 'cart';

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getSession();
    }

    public function getCart(): array
    {
        return $this->session->get(self::CART_KEY, []);
    }

    public function add(int $productId): void
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            $cart[$productId] = 0;
        }

        $cart[$productId]++;

        $this->session->set(self::CART_KEY, $cart);
    }

    public function decrease(int $productId): void
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return;
        }

        $cart[$productId]--;

        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }

        $this->session->set(self::CART_KEY, $cart);
    }

    public function remove(int $productId): void
    {
        $cart = $this->getCart();

        unset($cart[$productId]);

        $this->session->set(self::CART_KEY, $cart);
    }

    public function clear(): void
    {
        $this->session->remove(self::CART_KEY);
    }
}
