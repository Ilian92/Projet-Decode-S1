<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetAuthorControllerTest extends WebTestCase
{
    public function testGetAuthorSuccess(): void
    {
        $client = static::createClient();

        // Assume que l'auteur avec ID 1 existe dans la base de données de test
        $client->request('GET', '/database/get/author/1');

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Vérifier la structure de la réponse
        $this->assertArrayHasKey('id', $responseData);
        $this->assertArrayHasKey('firstName', $responseData);
        $this->assertArrayHasKey('lastName', $responseData);
        $this->assertArrayHasKey('biography', $responseData);
        $this->assertArrayHasKey('photoUrl', $responseData);
        $this->assertArrayHasKey('works', $responseData);

        // Vérifier que l'ID correspond
        $this->assertEquals(1, $responseData['id']);

        // Vérifier que works est un tableau
        $this->assertIsArray($responseData['works']);

        // Si des works existent, vérifier leur structure
        if (count($responseData['works']) > 0) {
            $this->assertArrayHasKey('id', $responseData['works'][0]);
            $this->assertArrayHasKey('title', $responseData['works'][0]);
        }
    }

    public function testGetAuthorNotFound(): void
    {
        $client = static::createClient();

        // Utiliser un ID qui n'existe probablement pas
        $client->request('GET', '/database/get/author/999999');

        $this->assertResponseStatusCodeSame(404);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Author not found', $responseData['error']);
    }

    public function testGetAuthorReturnsValidJson(): void
    {
        $client = static::createClient();

        $client->request('GET', '/database/get/author/1');

        $this->assertResponseIsSuccessful();

        $content = $client->getResponse()->getContent();
        $this->assertJson($content);

        // Vérifier que le JSON peut être décodé
        $data = json_decode($content, true);
        $this->assertNotNull($data);
    }

    public function testGetAuthorDataTypes(): void
    {
        $client = static::createClient();

        $client->request('GET', '/database/get/author/1');

        $this->assertResponseIsSuccessful();

        $responseData = json_decode($client->getResponse()->getContent(), true);

        // Vérifier les types de données
        $this->assertIsInt($responseData['id']);
        $this->assertIsString($responseData['firstName']);
        $this->assertIsString($responseData['lastName']);

        // biography et photoUrl peuvent être null
        if ($responseData['biography'] !== null) {
            $this->assertIsString($responseData['biography']);
        }

        if ($responseData['photoUrl'] !== null) {
            $this->assertIsString($responseData['photoUrl']);
        }

        $this->assertIsArray($responseData['works']);
    }
}
