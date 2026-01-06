<?php

namespace App\Service;

use App\Enum\ImageSize;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class OpenLibraryService
{
    private HttpClientInterface $client;
    private string $apiBaseUrl;
    private string $apiImageUrl;

    public function __construct(HttpClientInterface $client, string $apiBaseUrl, string $apiImageUrl)
    {
        $this->client = $client;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiImageUrl = $apiImageUrl;
    }

    private function getJson(string $endpoint, array $query = []): array
    {
        try {
            $response = $this->client->request('GET', $this->apiBaseUrl . $endpoint, [
                'query' => $query
            ]);

            // Throws exception if HTTP status is >= 400
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                throw new \Exception("API returned status code $statusCode");
            }

            $data = json_decode($response->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON returned: ' . json_last_error_msg());
            }

            return $data;
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            throw new \RuntimeException('Error calling Open Library API: ' . $e->getMessage());
        }
    }

    public function fetchWork(string $workID): array
    {
        return $this->getJson("/works/$workID.json");
    }

    public function fetchWorkBooks(string $workID): array
    {
        return $this->getJson("/works/$workID/editions.json");
    }

    public function fetchBook(string $bookID): array
    {
        return $this->getJson("/books/$bookID.json");
    }

    public function fetchAuthor(string $authorID): array
    {
        return $this->getJson("/authors/$authorID.json");
    }

    /**
     * ID can be authorID or bookID
     */
    public function fetchImage(string $ID, ImageSize $size, bool $isAuthor): string
    {

        $t = $isAuthor ? "a" : "b";

        $s = match ($size) {
            ImageSize::MEDIUM => "M",
            ImageSize::LARGE => "L",
            default => "S",
        };

        try {
            $response = $this->client->request('GET', $this->apiImageUrl . "$t/olid/$ID-$s.jpg", [
                'query' => null
            ]);

            // Throws exception if HTTP status is >= 400
            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                throw new \Exception("API returned status code $statusCode");
            }

            return $response->getContent();
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            throw new \RuntimeException('Error calling Open Library API: ' . $e->getMessage());
        }
    }

}
