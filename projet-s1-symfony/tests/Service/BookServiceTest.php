<?php

namespace App\Tests\Service;

use App\Entity\Book;
use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class BookServiceTest extends TestCase
{
    private OrderRepository $orderRepository;
    private EntityManagerInterface $entityManager;
    private BookService $bookService;

    protected function setUp(): void
    {
        $this->orderRepository = $this->createMock(OrderRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->bookService = new BookService($this->orderRepository, $this->entityManager);
    }

    public function testRestockUpdatesBookStockAndFlushes(): void
    {
        $book = new Book();
        $book->setId('olid-test');
        $book->setAvailableStock(5);

        $this->entityManager->expects(self::exactly(2))
            ->method('flush');

        $this->orderRepository->expects(self::once())
            ->method('findByBookStatus')
            ->with($book, OrderStatus::PENDING_SHIPMENT)
            ->willReturn([]);

        $this->bookService->restock($book, 10);

        $this->assertSame(10, $book->getAvailableStock());
    }

    public function testSyncOrdersWithBookStockWhenStockZeroUpdatesPendingShipmentToPendingRestock(): void
    {
        $book = new Book();
        $book->setId('olid-test');
        $book->setAvailableStock(0);

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);

        $this->orderRepository->expects(self::once())
            ->method('findByBookStatus')
            ->with($book, OrderStatus::PENDING_SHIPMENT)
            ->willReturn([$order]);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $updated = $this->bookService->syncOrdersWithBookStock($book);

        $this->assertSame(1, $updated);
        $this->assertSame(OrderStatus::PENDING_RESTOCK->value, $order->getStatus());
    }

    public function testSyncOrdersWithBookStockWhenStockPositiveUpdatesPendingRestockToPendingShipment(): void
    {
        $book = new Book();
        $book->setId('olid-test');
        $book->setAvailableStock(5);

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING_RESTOCK->value);

        $this->orderRepository->expects(self::once())
            ->method('findByBookStatus')
            ->with($book, OrderStatus::PENDING_RESTOCK)
            ->willReturn([$order]);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $updated = $this->bookService->syncOrdersWithBookStock($book);

        $this->assertSame(1, $updated);
        $this->assertSame(OrderStatus::PENDING_SHIPMENT->value, $order->getStatus());
    }

    public function testSyncOrdersWithBookStockReturnsZeroWhenNoOrders(): void
    {
        $book = new Book();
        $book->setId('olid-test');
        $book->setAvailableStock(0);

        $this->orderRepository->expects(self::once())
            ->method('findByBookStatus')
            ->with($book, OrderStatus::PENDING_SHIPMENT)
            ->willReturn([]);

        $this->entityManager->expects(self::never())
            ->method('flush');

        $updated = $this->bookService->syncOrdersWithBookStock($book);

        $this->assertSame(0, $updated);
    }
}
