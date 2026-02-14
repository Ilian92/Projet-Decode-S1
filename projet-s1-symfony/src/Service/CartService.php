<?php

namespace App\Service;

use App\Repository\BookRepository;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_COOKIE_NAME = 'mississippi_cart';
    private const CART_COOKIE_LIFETIME = 30 * 24 * 60 * 60;

    public function __construct(
        private RequestStack $requestStack,
        private BookRepository $bookRepository,
        private string $cartHmacSecret
    ) {
    }

    /**
     * Générer une signature HMAC pour valider l'intégrité du panier
     */
    private function generateSignature(string $data): string
    {
        return hash_hmac('sha256', $data, $this->cartHmacSecret);
    }

    /**
     * Vérifier la signature HMAC du panier
     */
    private function verifySignature(string $data, string $signature): bool
    {
        $expectedSignature = $this->generateSignature($data);
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Valider et nettoyer les données du panier
     */
    private function validateCart(array $cart): array
    {
        $validatedCart = [];

        foreach ($cart as $bookId => $quantity) {
            $bookId = (int) $bookId;
            if ($bookId <= 0) {
                continue;
            }

            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                continue;
            }

            $book = $this->bookRepository->find($bookId);
            if (!$book) {
                continue;
            }

            $availableStock = $book->getAvailableStock() ?? 0;
            if ($quantity > $availableStock) {
                $quantity = $availableStock;
            }

            if ($quantity > 0) {
                $validatedCart[$bookId] = $quantity;
            }
        }

        return $validatedCart;
    }

    /**
     * Obtenir le panier depuis le cookie avec validation de signature
     */
    public function getCart(): array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return [];
        }

        $cartCookie = $request->cookies->get(self::CART_COOKIE_NAME);
        if (!$cartCookie) {
            return [];
        }

        $parts = explode('.', $cartCookie);
        if (count($parts) !== 2) {
            return [];
        }

        [$cartData, $signature] = $parts;

        if (!$this->verifySignature($cartData, $signature)) {
            return [];
        }

        $cartJson = base64_decode($cartData);
        if ($cartJson === false) {
            return [];
        }

        $cart = json_decode($cartJson, true);
        if (!is_array($cart)) {
            return [];
        }

        return $this->validateCart($cart);
    }

    /**
     * Sauvegarder le panier dans un cookie avec signature HMAC
     */
    private function saveCart(array $cart): Cookie
    {
        $cart = $this->validateCart($cart);

        $cartJson = json_encode($cart);
        $cartData = base64_encode($cartJson);

        $signature = $this->generateSignature($cartData);

        $cookieValue = $cartData . '.' . $signature;

        return Cookie::create(self::CART_COOKIE_NAME)
            ->withValue($cookieValue)
            ->withExpires(time() + self::CART_COOKIE_LIFETIME)
            ->withPath('/')
            ->withSecure(false)
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }

    public function add(int $productId, int $quantity = 1): Cookie
    {
        if ($productId <= 0) {
            return $this->createCartCookie();
        }

        $book = $this->bookRepository->find($productId);
        if (!$book) {
            return $this->createCartCookie();
        }

        $availableStock = $book->getAvailableStock() ?? 0;
        if ($quantity > $availableStock) {
            $quantity = $availableStock;
        }

        if ($quantity <= 0) {
            return $this->createCartCookie();
        }

        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            $cart[$productId] = 0;
        }

        $cart[$productId] += $quantity;

        if ($cart[$productId] > $availableStock) {
            $cart[$productId] = $availableStock;
        }

        return $this->saveCart($cart);
    }

    public function createCartCookie(): Cookie
    {
        return $this->saveCart($this->getCart());
    }

    public function decrease(int $productId): Cookie
    {
        $cart = $this->getCart();

        if (!isset($cart[$productId])) {
            return $this->createCartCookie();
        }

        $cart[$productId]--;

        if ($cart[$productId] <= 0) {
            unset($cart[$productId]);
        }

        return $this->saveCart($cart);
    }

    public function remove(int $productId): Cookie
    {
        $cart = $this->getCart();
        unset($cart[$productId]);
        return $this->saveCart($cart);
    }

    public function clear(): Cookie
    {
        return $this->saveCart([]);
    }

    /**
     * Obtenir le panier avec les détails complets des livres
     * @return array<int, array{book: \App\Entity\Book, quantity: int}>
     */
    public function getCartWithDetails(): array
    {
        $cart = $this->getCart();
        $cartWithDetails = [];

        foreach ($cart as $bookId => $quantity) {
            $book = $this->bookRepository->find($bookId);
            if ($book) {
                $cartWithDetails[] = [
                    'book' => $book,
                    'quantity' => $quantity
                ];
            }
        }

        return $cartWithDetails;
    }

    /**
     * Obtenir le montant total du panier (en centimes)
     */
    public function getTotalAmount(): int
    {
        $total = 0;
        $cartWithDetails = $this->getCartWithDetails();

        foreach ($cartWithDetails as $item) {
            $price = $item['book']->getCurrentUnitPrice() ?? 0;
            $total += $price * $item['quantity'];
        }

        return $total;
    }

    /**
     * Obtenir le nombre total d'articles dans le panier
     */
    public function getTotalItems(): int
    {
        $cart = $this->getCart();
        return array_sum($cart);
    }

    /**
     * Modifier la quantité d'un article dans le panier
     */
    public function updateQuantity(int $productId, int $quantity): Cookie
    {
        if ($quantity <= 0) {
            return $this->remove($productId);
        }

        $book = $this->bookRepository->find($productId);
        if (!$book) {
            return $this->remove($productId);
        }

        $availableStock = $book->getAvailableStock() ?? 0;
        if ($quantity > $availableStock) {
            $quantity = $availableStock;
        }

        $cart = $this->getCart();
        $cart[$productId] = $quantity;

        return $this->saveCart($cart);
    }

    /**
     * Vérifier si le panier est vide
     */
    public function isEmpty(): bool
    {
        return empty($this->getCart());
    }

    /**
     * Obtenir la quantité d'un livre spécifique dans le panier
     */
    public function getItemQuantity(int $productId): int
    {
        $cart = $this->getCart();
        return $cart[$productId] ?? 0;
    }
}
