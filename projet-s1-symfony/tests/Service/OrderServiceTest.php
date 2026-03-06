<?php

namespace App\Tests\Service;

use App\Entity\Address;
use App\Entity\Book;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Enum\OrderStatus;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class OrderServiceTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private OrderService $orderService;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->orderService = new OrderService($this->entityManager);
    }

    public function testCreateReturnsNullWhenBookItemsEmpty(): void
    {
        $customer = new Customer();
        $customer->setAddress(new Address());

        $order = $this->orderService->create($customer, []);

        $this->assertNull($order);
    }

    public function testCreateReturnsNullWhenCustomerHasNoAddress(): void
    {
        $customer = new Customer();
        $customer->setAddress(null);

        $order = $this->orderService->create($customer, [
            ['book' => new Book(), 'quantity' => 1],
        ]);

        $this->assertNull($order);
    }

    public function testCreatePersistsOrderWhenBookItemsValid(): void
    {
        $book = new Book();
        $book->setId('olid1');
        $book->setCurrentUnitPrice(1000);
        $book->setAvailableStock(10);

        $address = new Address();
        $address->setStreet('1 rue Test');
        $address->setCity('Paris');
        $address->setPostalCode('75001');
        $address->setCountry('France');
        $address->setRecipientName('John');
        $address->setLabel('Home');
        $address->setAddressDetails('Apt 1');

        $customer = new Customer();
        $customer->setAddress($address);

        $bookItems = [
            ['book' => $book, 'quantity' => 2],
        ];

        $this->entityManager->expects(self::exactly(2))
            ->method('persist')
            ->willReturnCallback(function ($entity) {
                static::assertTrue(
                    $entity instanceof Order || $entity instanceof OrderLine,
                    'persist should be called with Order or OrderLine'
                );
            });
        $this->entityManager->expects(self::once())
            ->method('flush');

        $order = $this->orderService->create($customer, $bookItems);

        $this->assertInstanceOf(Order::class, $order);
        $this->assertSame($customer, $order->getCustomer());
        $this->assertSame($address, $order->getAddress());
        $this->assertSame(OrderStatus::PENDING_RESTOCK->value, $order->getStatus());
        $this->assertSame(500, $order->getShippingCost());
        $this->assertSame(2000 + 500, $order->getTotalAmount());
        $this->assertCount(1, $order->getOrderLine());
    }

    public function testShipReturnsFalseWhenStatusNotPendingShipment(): void
    {
        $this->entityManager->expects(self::never())
            ->method('flush');

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING_RESTOCK->value);

        $result = $this->orderService->ship($order);

        $this->assertFalse($result);
    }

    public function testShipReturnsFalseWhenOrderNotFulfillable(): void
    {
        $book = new Book();
        $book->setId('olid1');
        $book->setAvailableStock(0);

        $line = new OrderLine();
        $line->setBook($book);
        $line->setQuantity(2);

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);
        $order->addOrderLine($line);

        $result = $this->orderService->ship($order);

        $this->assertFalse($result);
    }

    public function testShipUpdatesStockAndStatusWhenFulfillable(): void
    {
        $book = new Book();
        $book->setId('olid1');
        $book->setAvailableStock(5);

        $line = new OrderLine();
        $line->setBook($book);
        $line->setQuantity(2);

        $order = new Order();
        $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);
        $order->addOrderLine($line);

        $this->entityManager->expects(self::once())
            ->method('flush');

        $result = $this->orderService->ship($order);

        $this->assertTrue($result);
        $this->assertSame(3, $book->getAvailableStock());
        $this->assertSame(OrderStatus::SHIPPED->value, $order->getStatus());
        $this->assertStringStartsWith('TRK-', $order->getTrackingNumber());
    }
}
