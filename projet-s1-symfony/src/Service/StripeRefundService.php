<?php

namespace App\Service;

use App\Entity\Order;
use App\Enum\OrderStatus;
use Stripe\Refund;
use Stripe\Stripe;
use Doctrine\ORM\EntityManagerInterface;

class StripeRefundService
{
    public function __construct(
        private string $stripeSecretKey,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function refundOrder(Order $order): bool
    {
        $paymentIntentId = $order->getStripePaymentIntentId();
        if ($paymentIntentId === null || $paymentIntentId === '') {
            return false;
        }

        if ($order->getStatus() === OrderStatus::REFUNDED->value) {
            return false;
        }

        if ($order->getStatus() === OrderStatus::PAYMENT_PENDING->value) {
            return false;
        }

        if ($this->stripeSecretKey === '') {
            return false;
        }

        Stripe::setApiKey($this->stripeSecretKey);

        try {
            Refund::create(
                [
                    'payment_intent' => $paymentIntentId,
                    'metadata' => [
                        'order_id' => (string) $order->getId(),
                    ],
                ],
                [
                    'idempotency_key' => 'refund-order-' . $order->getId(),
                ]
            );
        } catch (\Throwable) {
            return false;
        }

        $order->setStatus(OrderStatus::REFUNDED->value);
        $this->entityManager->flush();

        return true;
    }
}
