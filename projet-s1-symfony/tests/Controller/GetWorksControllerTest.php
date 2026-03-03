<?php

namespace App\Tests\Controller;

use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetWorksControllerTest extends WebTestCase
{
    public function testGetWorksReturnsSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/works');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetWorksReturnsJson(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/works');

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetWorksReturnsArray(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/works');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);
    }

    public function testGetWorksContainsInsertedWork(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_WORKS_TEST_001');
        $work->setTitle('Notre-Dame de Paris');
        $work->setSummary('Un roman historique de Victor Hugo.');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/works');

        $this->assertResponseStatusCodeSame(200);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertIsArray($data);

        $found = array_filter($data, fn($w) => $w['id'] === 'OL_WORKS_TEST_001');
        $this->assertNotEmpty($found, 'L\'œuvre insérée doit être présente dans la réponse.');
    }

    public function testGetWorksItemStructure(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_WORKS_TEST_002');
        $work->setTitle('Les Fleurs du Mal');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/works');

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertNotEmpty($data);

        $first = $data[0];
        $this->assertArrayHasKey('id', $first);
        $this->assertArrayHasKey('title', $first);
        $this->assertArrayHasKey('summary', $first);
        $this->assertArrayHasKey('genres', $first);
        $this->assertArrayHasKey('authors', $first);
        $this->assertArrayHasKey('books', $first);
        $this->assertIsArray($first['genres']);
        $this->assertIsArray($first['authors']);
        $this->assertIsArray($first['books']);
    }

    public function testGetWorksCountIncreasesAfterInsertion(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $client->request('GET', '/database/get/works');
        $beforeData = json_decode($client->getResponse()->getContent(), true);
        $countBefore = count($beforeData);

        $work = new Work();
        $work->setId('OL_WORKS_TEST_003');
        $work->setTitle('Madame Bovary');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/works');
        $afterData = json_decode($client->getResponse()->getContent(), true);
        $countAfter = count($afterData);

        $this->assertEquals($countBefore + 1, $countAfter);
    }

    public function testGetWorksWithInvalidMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/database/get/works');

        $this->assertResponseStatusCodeSame(405);
    }
}

