<?php

namespace App\Service;

use App\Entity\Book;
use App\Enum\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookService
{
    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function restock(Book $book, int $newQuantity): void
    {
        $book->setAvailableStock($newQuantity);
        $this->entityManager->flush();
        $this->syncOrdersWithBookStock($book);
    }

    public function syncOrdersWithBookStock(Book $book): int
    {
        $availableStock = $book->getAvailableStock() ?? 0;
        $updated = 0;

        if ($availableStock === 0) {
            foreach ($this->orderRepository->findByBookStatus($book, OrderStatus::PENDING_SHIPMENT) as $order) {
                $order->setStatus(OrderStatus::PENDING_RESTOCK->value);
                $this->entityManager->flush();
                ++$updated;
            }
        } else {
            foreach ($this->orderRepository->findByBookStatus($book, OrderStatus::PENDING_RESTOCK) as $order) {
                $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);
                $this->entityManager->flush();
                ++$updated;
            }
        }

        return $updated;
    }
}
