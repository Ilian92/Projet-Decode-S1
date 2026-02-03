<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegisterControllerTest extends WebTestCase
{
    public function testRegisterPageLoads(): void
    {
        $client = static::createClient();

        $client->request('GET', '/register');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
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
