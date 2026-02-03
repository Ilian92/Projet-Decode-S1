<?php

namespace App\Tests\Controller;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetAuthorControllerTest extends WebTestCase
{
    public function testGetAuthorReturnsSuccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $author = new Author();
        $author->setFirstName('Victor');
        $author->setLastName('Hugo');

        $em->persist($author);
        $em->flush();

        $client->request('GET', '/database/get/author/' . $author->getId());

        $this->assertResponseStatusCodeSame(200);
        $this->assertResponseIsSuccessful();
    }

    public function testGetAuthorReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/author/1');

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }
    
    public function testGetAuthorNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/author/999999');

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Author not found', $data['error']);
    }

    public function testGetAuthorWithInvalidMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/database/get/author/1');

        $this->assertResponseStatusCodeSame(405);
    }
}
