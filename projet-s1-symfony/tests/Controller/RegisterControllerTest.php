<?php

namespace App\Tests\Controller;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $customer = $em->getRepository(Customer::class)->findOneBy(['email' => 'ilian.leboss@mail.com']);
        if ($customer) {
            $em->remove($customer);
            $em->flush();
        }

        parent::tearDown();
    }

    public function testRegisterWithMissingFields(): void
    {
        $client = static::createClient();

        $client->request('POST', '/register', [
            'email' => 'test@example.com',
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('body', 'Veuillez remplir tous les champs obligatoires');
    }

    public function testRegisterSuccess(): void
    {
        $client = static::createClient();

        $client->request('POST', '/register', [
            'firstName' => 'Ilian',
            'lastName' => 'Igoudjil',
            'email' => 'ilian.leboss@mail.com',
            'phone' => '0612345678',
            'password' => 'MotDePasse123!'
        ]);

        $this->assertResponseRedirects();
    }
}
