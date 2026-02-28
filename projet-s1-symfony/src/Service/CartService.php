<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;

class CartService
{
    private const CART_COOKIE_NAME = 'mississippi_cart';
    private const CART_COOKIE_LIFETIME = 30 * 24 * 60 * 60;

    private const DEFAULT_EDITION_UNIT_PRICE_CENTIMES = 999;

    public function __construct(
        private RequestStack $requestStack,
        private BookRepository $bookRepository,
        private WorkRepository $workRepository,
        private OpenLibraryService $openLibraryService,
        private EntityManagerInterface $entityManager,
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

        foreach ($cart as $bookOlid => $quantity) {
            if (!\is_string($bookOlid) || $bookOlid === '') {
                continue;
            }

            $quantity = (int) $quantity;
            if ($quantity <= 0) {
                continue;
            }

            $book = $this->bookRepository->find($bookOlid);
            if (!$book) {
                continue;
            }

            $availableStock = $book->getAvailableStock() ?? 0;
            if ($availableStock > 0 && $quantity > $availableStock) {
                $quantity = $availableStock;
            }

            $validatedCart[$book->getId()] = $quantity;
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

    public function add(string $bookOlid, string $workOlid, int $quantity = 1): Cookie
    {
        if ($bookOlid === '' || $workOlid === '' || $quantity <= 0) {
            return $this->createCartCookie();
        }

        $book = $this->findOrCreateBookByOlid($bookOlid, $workOlid);
        if (!$book) {
            return $this->createCartCookie();
        }

        $olid = $book->getId();
        $availableStock = $book->getAvailableStock() ?? 0;
        if ($availableStock > 0 && $quantity > $availableStock) {
            $quantity = $availableStock;
        }

        $cart = $this->getCart();
        $cart[$olid] = ($cart[$olid] ?? 0) + $quantity;

        if ($availableStock > 0 && $cart[$olid] > $availableStock) {
            $cart[$olid] = $availableStock;
        }

        return $this->saveCart($cart);
    }

    public function findOrCreateBookByOlid(string $bookOlid, string $workOlid): ?Book
    {
        if ($bookOlid === '') {
            return null;
        }

        $book = $this->bookRepository->find($bookOlid);
        if ($book !== null) {
            return $book;
        }

        $work = $this->workRepository->find($workOlid);
        if (!$work) {
            return null;
        }

        try {
            $editionData = $this->openLibraryService->fetchBook($bookOlid);
        } catch (\Throwable) {
            return null;
        }

        $formatted = $this->openLibraryService->formatEditionForFrontend($editionData);

        $book = new Book();
        $book->setId($bookOlid);
        $book->setWork($work);
        $book->setAvailableStock(0);
        $book->setCurrentUnitPrice(self::DEFAULT_EDITION_UNIT_PRICE_CENTIMES);

        $publishDate = $this->parsePublicationDate($formatted['publish_date'] ?? null);
        $book->setPublicationDate($publishDate);

        if (!empty($formatted['cover_url'])) {
            $book->setCoverImageUrl($formatted['cover_url']);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    private function parsePublicationDate(?string $publishDate): \DateTime
    {
        if ($publishDate !== null && preg_match('/\d{4}/', $publishDate, $m)) {
            $year = (int) $m[0];
            $year = max(1, min(9999, $year));

            return new \DateTime("$year-01-01");
        }

        return new \DateTime('today');
    }

    public function remove(string $editionKey): Cookie
    {
        $cart = $this->getCart();
        unset($cart[$editionKey]);

        return $this->saveCart($cart);
    }

    public function createCartCookie(): Cookie
    {
        return $this->saveCart($this->getCart());
    }

    public function decrease(string $bookOlid): Cookie
    {
        $cart = $this->getCart();

        if (!isset($cart[$bookOlid])) {
            return $this->createCartCookie();
        }

        $cart[$bookOlid]--;

        if ($cart[$bookOlid] <= 0) {
            unset($cart[$bookOlid]);
        }

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

        foreach ($cart as $bookOlid => $quantity) {
            $book = $this->bookRepository->find($bookOlid);
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
    public function updateQuantity(string $bookOlid, int $quantity): Cookie
    {
        if ($quantity <= 0) {
            return $this->remove($bookOlid);
        }

        $book = $this->bookRepository->find($bookOlid);
        if (!$book) {
            return $this->remove($bookOlid);
        }

        $availableStock = $book->getAvailableStock() ?? 0;
        if ($availableStock > 0 && $quantity > $availableStock) {
            $quantity = $availableStock;
        }

        $cart = $this->getCart();
        $cart[$bookOlid] = $quantity;

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
    public function getItemQuantity(string $bookOlid): int
    {
        $cart = $this->getCart();

        return $cart[$bookOlid] ?? 0;
    }
}
