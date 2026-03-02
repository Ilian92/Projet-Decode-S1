<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Customer;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrderService
{
    private const SHIPPING_COST_CENTIMES = 500;

    public function __construct(
        private CartService $cartService,
        private BookService $bookService,
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(Customer $customer): ?Order
    {
        $cart = $this->cartService->getCart();
        if (empty($cart)) {
            return null;
        }

        $cartItems = [];
        foreach ($cart as $olid => $quantity) {
            $book = $this->bookService->createFromOlid($olid);
            if ($book === null) {
                continue;
            }
            $availableStock = $book->getAvailableStock() ?? 0;
            if ($availableStock > 0 && $quantity > $availableStock) {
                $quantity = $availableStock;
            }
            $cartItems[] = ['book' => $book, 'quantity' => $quantity];
        }

        if (empty($cartItems)) {
            return null;
        }

        $address = $customer->getAddress();
        if ($address === null) {
            return null;
        }

        $order = new Order();
        $order->setCustomer($customer);
        $order->setAddress($address);
        $order->setOrderDate(new \DateTime());
        $order->setStatus(OrderStatus::PENDING_RESTOCK->value);
        $order->setShippingCost(self::SHIPPING_COST_CENTIMES);
        $order->setTrackingNumber('');

        $subtotal = 0;
        foreach ($cartItems as $item) {
            $book = $item['book'];
            $quantity = $item['quantity'];
            $unitPrice = $book->getCurrentUnitPrice() ?? 0;
            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;

            $line = new OrderLine();
            $line->setBook($book);
            $line->setQuantity($quantity);
            $line->setUnitPriceSnapshot($unitPrice);
            $line->setTableOrder($order);
            $order->addOrderLine($line);
            $this->entityManager->persist($line);
        }

        $order->setTotalAmount($subtotal + self::SHIPPING_COST_CENTIMES);
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        return $order;
    }

    public function ship(Order $order): bool
    {
        if ($order->getStatus() !== OrderStatus::PENDING_SHIPMENT->value) {
            return false;
        }

        if (!$order->isFulfillable()) {
            return false;
        }

        foreach ($order->getOrderLine() as $line) {
            $book = $line->getBook();
            if ($book === null) {
                continue;
            }
            $book->setAvailableStock(($book->getAvailableStock() ?? 0) - $line->getQuantity());
        }

        $order->setTrackingNumber($this->generateTrackingNumber($order));
        $order->setStatus(OrderStatus::SHIPPED->value);
        $this->entityManager->flush();

        return true;
    }

    private function generateTrackingNumber(Order $order): string
    {
        return sprintf('TRK-%d-%s', $order->getId(), date('YmdHis'));
    }
}
