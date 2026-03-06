<?php

namespace App\Tests\Service;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\WorkRepository;
use App\Service\BookService;
use App\Service\CartService;
use App\Service\OpenLibraryService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

class CartServiceTest extends TestCase
{
    private const CART_HMAC_SECRET = 'test-secret-key';

    private RequestStack $requestStack;
    private BookRepository $bookRepository;
    private BookService $bookService;
    private WorkRepository $workRepository;
    private OpenLibraryService $openLibraryService;
    private EntityManagerInterface $entityManager;
    private CartService $cartService;

    protected function setUp(): void
    {
        $this->requestStack = new RequestStack();
        $this->bookRepository = $this->createMock(BookRepository::class);
        $this->bookService = $this->createMock(BookService::class);
        $this->workRepository = $this->createMock(WorkRepository::class);
        $this->openLibraryService = $this->createMock(OpenLibraryService::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->cartService = new CartService(
            $this->requestStack,
            $this->bookRepository,
            $this->bookService,
            $this->workRepository,
            $this->openLibraryService,
            $this->entityManager,
            self::CART_HMAC_SECRET
        );
    }

    public function testGetCartReturnsEmptyWhenNoRequest(): void
    {
        $this->requestStack->push(new Request());

        // Pop so there is no current request when getCart runs
        $this->requestStack->pop();

        $cart = $this->cartService->getCart();

        $this->assertSame([], $cart);
    }

    public function testGetCartReturnsEmptyWhenNoCookie(): void
    {
        $this->requestStack->push(new Request());

        $cart = $this->cartService->getCart();

        $this->assertSame([], $cart);
    }

    public function testGetCartReturnsEmptyWhenCookieHasInvalidFormat(): void
    {
        $request = new Request();
        $request->cookies->set('mississippi_cart', 'invalid-no-dot');
        $this->requestStack->push($request);

        $cart = $this->cartService->getCart();

        $this->assertSame([], $cart);
    }

    public function testGetCartReturnsEmptyWhenSignatureInvalid(): void
    {
        $cartData = base64_encode(json_encode(['olid1' => 2]));
        $request = new Request();
        $request->cookies->set('mississippi_cart', $cartData . '.wrong-signature');
        $this->requestStack->push($request);

        $cart = $this->cartService->getCart();

        $this->assertSame([], $cart);
    }

    public function testGetCartReturnsValidatedCartWhenCookieValid(): void
    {
        $cartJson = json_encode(['olid1' => 2]);
        $cartData = base64_encode($cartJson);
        $signature = hash_hmac('sha256', $cartData, self::CART_HMAC_SECRET);
        $cookieValue = $cartData . '.' . $signature;

        $request = new Request();
        $request->cookies->set('mississippi_cart', $cookieValue);
        $this->requestStack->push($request);

        $cart = $this->cartService->getCart();

        $this->assertSame(['olid1' => 2], $cart);
    }

    public function testGetShippingCostReturnsConstant(): void
    {
        $this->requestStack->push(new Request());

        $cost = $this->cartService->getShippingCost();

        $this->assertSame(500, $cost);
    }

    public function testGetTotalAmountIsArticleAmountPlusShipping(): void
    {
        $book = new Book();
        $book->setId('olid1');
        $book->setAvailableStock(10);
        $book->setCurrentUnitPrice(1000); // 10.00

        $this->bookRepository->expects(self::atLeastOnce())
            ->method('find')
            ->with('olid1')
            ->willReturn($book);

        $cartJson = json_encode(['olid1' => 2]);
        $cartData = base64_encode($cartJson);
        $signature = hash_hmac('sha256', $cartData, self::CART_HMAC_SECRET);
        $request = new Request();
        $request->cookies->set('mississippi_cart', $cartData . '.' . $signature);
        $this->requestStack->push($request);

        $total = $this->cartService->getTotalAmount();

        $this->assertSame(2000 + 500, $total); // 2 * 1000 + shipping
    }

    public function testGetTotalItemsSumsQuantities(): void
    {
        $cartJson = json_encode(['olid1' => 3]);
        $cartData = base64_encode($cartJson);
        $signature = hash_hmac('sha256', $cartData, self::CART_HMAC_SECRET);
        $request = new Request();
        $request->cookies->set('mississippi_cart', $cartData . '.' . $signature);
        $this->requestStack->push($request);

        $total = $this->cartService->getTotalItems();

        $this->assertSame(3, $total);
    }

    public function testIsEmptyReturnsTrueWhenNoCart(): void
    {
        $this->requestStack->push(new Request());

        $this->assertTrue($this->cartService->isEmpty());
    }

    public function testGetItemQuantityReturnsZeroWhenNotInCart(): void
    {
        $this->requestStack->push(new Request());

        $qty = $this->cartService->getItemQuantity('unknown');

        $this->assertSame(0, $qty);
    }

    public function testClearReturnsCookie(): void
    {
        $this->requestStack->push(new Request());

        $cookie = $this->cartService->clear();

        $this->assertSame('mississippi_cart', $cookie->getName());
        $this->assertNotEmpty($cookie->getValue());
    }

    public function testAddWithEmptyBookOlidReturnsCookie(): void
    {
        $this->requestStack->push(new Request());

        $cookie = $this->cartService->add('', 'work1', 1);

        $this->assertSame('mississippi_cart', $cookie->getName());
    }

    public function testRemoveReturnsCookie(): void
    {
        $this->requestStack->push(new Request());

        $cookie = $this->cartService->remove('some-key');

        $this->assertSame('mississippi_cart', $cookie->getName());
    }

    public function testDecreaseWhenItemNotInCartReturnsCookie(): void
    {
        $this->requestStack->push(new Request());

        $cookie = $this->cartService->decrease('unknown');

        $this->assertSame('mississippi_cart', $cookie->getName());
    }

    public function testCreateCartCookieReturnsCookie(): void
    {
        $this->requestStack->push(new Request());

        $cookie = $this->cartService->createCartCookie();

        $this->assertSame('mississippi_cart', $cookie->getName());
    }
}
