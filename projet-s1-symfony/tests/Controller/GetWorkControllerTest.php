<?php

namespace App\Tests\Controller;

use App\Entity\Work;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetWorkControllerTest extends WebTestCase
{
    protected function tearDown(): void
    {
        $em = static::getContainer()->get(EntityManagerInterface::class);
        $works = $em->getRepository(Work::class)->findAll();
        foreach ($works as $work) {
            $em->remove($work);
        }
        $em->flush();

        parent::tearDown();
    }

    public function testGetWorkReturnsSuccess(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_TEST_001');
        $work->setTitle('L\'Étranger');
        $work->setSummary('Un roman d\'Albert Camus.');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/work/OL_TEST_001');

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(200);
    }

    public function testGetWorkReturnsJson(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_TEST_002');
        $work->setTitle('La Nausée');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/work/OL_TEST_002');

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testGetWorkReturnsCorrectData(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_TEST_003');
        $work->setTitle('Le Petit Prince');
        $work->setSummary('Un conte philosophique.');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/work/OL_TEST_003');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertEquals('OL_TEST_003', $data['id']);
        $this->assertEquals('Le Petit Prince', $data['title']);
        $this->assertEquals('Un conte philosophique.', $data['summary']);
    }

    public function testGetWorkStructure(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $work = new Work();
        $work->setId('OL_TEST_004');
        $work->setTitle('Germinal');

        $em->persist($work);
        $em->flush();

        $client->request('GET', '/database/get/work/OL_TEST_004');

        $data = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('title', $data);
        $this->assertArrayHasKey('summary', $data);
        $this->assertArrayHasKey('genres', $data);
        $this->assertArrayHasKey('authors', $data);
        $this->assertArrayHasKey('books', $data);
        $this->assertIsArray($data['genres']);
        $this->assertIsArray($data['authors']);
        $this->assertIsArray($data['books']);
    }

    public function testGetWorkNotFound(): void
    {
        $client = static::createClient();
        $client->request('GET', '/database/get/work/OL_DOES_NOT_EXIST');

        $this->assertResponseStatusCodeSame(404);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('error', $data);
        $this->assertEquals('Work not found', $data['error']);
    }

    public function testGetWorkWithInvalidMethod(): void
    {
        $client = static::createClient();
        $client->request('POST', '/database/get/work/OL_TEST_001');

        $this->assertResponseStatusCodeSame(405);
    }
}

