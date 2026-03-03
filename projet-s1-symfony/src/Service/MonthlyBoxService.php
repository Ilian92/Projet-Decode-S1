<?php

namespace App\Service;

use App\Entity\MonthlyBox;
use App\Entity\Order;
use App\Entity\Subscription;
use App\Repository\SubscriptionRepository;
use Doctrine\ORM\EntityManagerInterface;

class MonthlyBoxService
{
    public function __construct(
        private SubscriptionRepository $subscriptionRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function create(Subscription $subscription, ?string $referenceMonth = null, ?Order $order = null): MonthlyBox
    {
        $monthlyBox = new MonthlyBox();
        $monthlyBox->setSubscription($subscription);
        $monthlyBox->setReferenceMonth($referenceMonth ?? (new \DateTimeImmutable())->format('Y-m'));
        $monthlyBox->setCreationDate(new \DateTime());
        $monthlyBox->setTableOrder($order);

        $this->entityManager->persist($monthlyBox);
        $this->entityManager->flush();

        return $monthlyBox;
    }
}
