<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BookRelatedDBInsertionControllerTest extends WebTestCase
{
    public function testInsertionReturnsJson(): void
    {
        $client = static::createClient();

        $data = [
            'authors' => [
                ['firstName' => 'Victor', 'lastName' => 'Hugo']
            ],
            'genres' => [
                ['label' => 'Roman']
            ],
            'work' => [
                'title' => 'Les Misérables',
                'summary' => 'Un roman historique',
                'authorIds' => [0],
                'genreIds' => [0]
            ],
            'bookPublisher' => [
                'publisherName' => 'Gallimard'
            ],
            'book' => [
                'publicationDate' => '2024-01-01',
                'currentUnitPrice' => 1500,
                'availableStock' => 10
            ]
        ];

        $client->request(
            'POST',
            '/database/insertion',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseHeaderSame('content-type', 'application/json');
    }

    public function testInsertionWithInvalidJson(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/database/insertion',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            'invalid json'
        );

        $this->assertResponseStatusCodeSame(400);

        $data = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(false, $data['success']);
        $this->assertEquals('Invalid JSON format', $data['error']);
    }

    public function testInsertionWithInvalidMethod(): void
    {
        $client = static::createClient();

        $client->request('GET', '/database/insertion');

        $this->assertResponseStatusCodeSame(405);
    }

    public function testInsertionSuccess(): void
    {
        $client = static::createClient();

        $data = [
            'authors' => [
                ['firstName' => 'Albert', 'lastName' => 'Camus']
            ]
        ];

        $client->request(
            'POST',
            '/database/insertion',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(201);

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals(true, $responseData['success']);
        $this->assertArrayHasKey('ids', $responseData);
    }
}
