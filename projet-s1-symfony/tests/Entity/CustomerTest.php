<?php

namespace App\Tests\Entity;

use App\Entity\Customer;
use App\Entity\Subscription;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    // public function testAddSubscription(): void
    // {
    //     $customer = new Customer();
    //     $subscription = new Subscription();

    //     $customer->addSubscription($subscription);

    //     $this->assertCount(1, $customer->getSubscriptions());
    //     $this->assertEquals($customer, $subscription->getCustomer());
    // }

    public function testAddAndRemoveSubscription(): void
    {
        $customer = new Customer();
        $subscription = new Subscription();

        $customer->addSubscription($subscription);

        $this->assertCount(1, $customer->getSubscriptions());
        $this->assertEquals($customer, $subscription->getCustomer());

        $customer->removeSubscription($subscription);

        $this->assertCount(0, $customer->getSubscriptions());
        $this->assertNull($subscription->getCustomer());
    }
}
