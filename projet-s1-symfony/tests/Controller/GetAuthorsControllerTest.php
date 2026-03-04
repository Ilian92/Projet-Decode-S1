<?php

namespace App\Tests\Controller;

use App\Entity\Author;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetAuthorsControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $authors = $em->getRepository(Author::class)->findAll();
        foreach ($authors as $author) {
            $em->remove($author);
        }
        $em->flush();

        parent::tearDown();
    }

    public function testGetAuthorsReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/authors');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetAuthorsReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/authors');

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetAuthorsReturnsArray(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/authors');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetAuthorsContainsInsertedAuthor(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $author = new Author();
        $author->setFirstName('Émile');
        $author->setLastName('Zola');
        $author->setBiography('Romancier naturaliste français.');
        $author->setPhotoUrl('https://example.com/zola.jpg');

        $em->persist($author);
        $em->flush();

        $client->request('GET', '/database/get/authors');

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $found = array_filter($data, fn($a) => $a['firstName'] === 'Émile' && $a['lastName'] === 'Zola');
        $this->assertNotEmpty($found, 'L\'auteur inséré doit être présent dans la réponse.');
    }

    public function testGetAuthorsItemStructure(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $author = new Author();
        $author->setFirstName('Marcel');
        $author->setLastName('Proust');

        $em->persist($author);
        $em->flush();

        $client->request('GET', '/database/get/authors');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($data);

        $first = $data[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('firstName', $first);
        $this->assertArrayHasKey('lastName', $first);
        $this->assertArrayHasKey('biography', $first);
        $this->assertArrayHasKey('photoUrl', $first);
        $this->assertArrayHasKey('works', $first);
        $this->assertIsArray($first['works']);
    }

    public function testGetAuthorsWithInvalidMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/database/get/authors');

        $this->assertResponseStatusCodeSame(405);
    }
}

