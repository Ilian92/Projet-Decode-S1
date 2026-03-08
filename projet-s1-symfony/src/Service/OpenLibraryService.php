<?php

namespace App\Service;

use App\Enum\ImageSize;
use App\Enum\ImageType;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;

class OpenLibraryService
{
    private HttpClientInterface $client;
    private string $apiBaseUrl;
    private string $apiImageUrl;
    private CacheItemPoolInterface $cache;

    public function __construct(
        HttpClientInterface $client,
        string $apiBaseUrl,
        string $apiImageUrl,
        CacheItemPoolInterface $cache
    ) {
        $this->client = $client;
        $this->apiBaseUrl = $apiBaseUrl;
        $this->apiImageUrl = $apiImageUrl;
        $this->cache = $cache;
    }

    private function getJson(string $endpoint, array $query = [], int $cacheTtl = 3600): array
    {
        $cacheKey = 'openlibrary_' . md5($endpoint . serialize($query));

        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $response = $this->client->request('GET', $this->apiBaseUrl . $endpoint, [
                'query' => $query,
                'timeout' => 5
            ]);

            $statusCode = $response->getStatusCode();
            if ($statusCode >= 400) {
                throw new \Exception("API returned status code $statusCode");
            }

            $data = json_decode($response->getContent(), true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Invalid JSON returned: ' . json_last_error_msg());
            }

            $cacheItem->set($data);
            $cacheItem->expiresAfter($cacheTtl);
            $this->cache->save($cacheItem);

            return $data;
        } catch (TransportExceptionInterface | ClientExceptionInterface | ServerExceptionInterface $e) {
            throw new \RuntimeException('Error calling Open Library API: ' . $e->getMessage());
        }
    }

    public function fetchWork(string $workID): array
    {
        return $this->getJson("/works/$workID.json");
    }

    public function fetchWorkBooks(string $workID, ?int $limit = null, int $offset = 0): array
    {
        $query = [];
        if ($limit !== null) {
            $query['limit'] = $limit;
        }
        if ($offset > 0) {
            $query['offset'] = $offset;
        }
        return $this->getJson("/works/$workID/editions.json", $query);
    }

    public function fetchBook(string $bookID): array
    {
        return $this->getJson("/books/$bookID.json");
    }

    public function fetchAuthor(string $authorID): array
    {
        return $this->getJson("/authors/$authorID.json");
    }


    public function fetchWorkRatings(string $workID): array
    {
        return $this->getJson("/works/$workID/ratings.json");
    }

    /**
     * ID can be authorID or coverID
     */
    public function getImageUrl(string $ID, ImageType $type, ImageSize $size): string
    {
        $idType = $type === ImageType::AUTHOR ? 'olid' : 'id';
        return $this->apiImageUrl . "{$type->getSuffix()}/$idType/$ID-{$size->getSuffix()}.jpg";
    }

    public function search(array $params = []): array
    {
        return $this->getJson("/search.json", $params);
    }

    public function searchSubject(string $subject, int $limit = 10, int $offset = 0): array
    {
        return $this->getJson("/subjects/" . rawurlencode($subject) . ".json", [
            'limit' => $limit,
            'offset' => $offset,
        ]);
    }

    /**
     * Format a work from OpenLibrary API for frontend display
     * Handles both search API format and subject works format
     *
     * @param array $work Work data from OpenLibrary API
     * @return array Formatted work data
     */
    public function formatWorkForFrontend(array $work): array
    {
        $coverId = $work['cover_i'] ?? $work['cover_id'] ?? null;
        if (!$coverId && isset($work['covers']) && !empty($work['covers'])) {
            $coverId = is_array($work['covers']) ? $work['covers'][0] : $work['covers'];
        }

        $coverUrl = null;
        if ($coverId) {
            $coverUrl = $this->getImageUrl($coverId, ImageType::BOOK, ImageSize::MEDIUM);
        }

        $authorKey = null;
        $authorName = null;

        if (isset($work['author_name']) && is_array($work['author_name']) && !empty($work['author_name'])) {
            // Search API format - author_name is an array
            $authorName = $work['author_name'][0];
            $authorKey = $work['author_key'][0] ?? null;
        } elseif (isset($work['author_key']) && is_array($work['author_key']) && !empty($work['author_key'])) {
            $authorKey = $work['author_key'][0];
            $authorName = null;
        } elseif (isset($work['authors']) && is_array($work['authors']) && !empty($work['authors'])) {
            $firstAuthor = $work['authors'][0];
            if (is_array($firstAuthor)) {
                $authorName = $firstAuthor['name'] ?? null;
                $authorKey = $firstAuthor['key'] ?? null;
            } else {
                $authorName = $firstAuthor;
            }
        }

        return [
            'key' => $work['key'] ?? null,
            'title' => $work['title'] ?? 'Untitled',
            'author' => $authorName,
            'author_key' => $authorKey,
            'first_publish_year' => $work['first_publish_year'] ?? null,
            'edition_count' => $work['edition_count'] ?? 0,
            'cover_url' => $coverUrl,
            'has_fulltext' => $work['has_fulltext'] ?? false,
            'ia' => $work['ia'] ?? [],
        ];
    }

    /**
     * Format an edition/book from OpenLibrary API for frontend display
     *
     * @param array $edition Edition data from OpenLibrary API
     * @return array Formatted edition data
     */
    public function formatEditionForFrontend(array $edition): array
    {
        $coverId = null;
        if (isset($edition['covers']) && !empty($edition['covers'])) {
            $coverId = $edition['covers'][0];
        }

        $coverUrl = null;
        if ($coverId) {
            $coverUrl = $this->getImageUrl($coverId, ImageType::BOOK, ImageSize::MEDIUM);
        }

        $authors = [];
        if (isset($edition['authors'])) {
            foreach ($edition['authors'] as $author) {
                $authors[] = [
                    'name' => $author['name'] ?? 'Unknown',
                    'key' => $author['key'] ?? null,
                ];
            }
        }

        return [
            'key' => $edition['key'] ?? null,
            'title' => $edition['title'] ?? 'Untitled',
            'subtitle' => $edition['subtitle'] ?? null,
            'authors' => $authors,
            'publish_date' => $edition['publish_date'] ?? null,
            'publishers' => $edition['publishers'] ?? [],
            'isbn_10' => $edition['isbn_10'][0] ?? null,
            'isbn_13' => $edition['isbn_13'][0] ?? null,
            'number_of_pages' => $edition['number_of_pages'] ?? null,
            'cover_url' => $coverUrl,
            'description' => $edition['description']['value'] ?? $edition['description'] ?? null,
        ];
    }

    public function extractOlid(string $key): ?string
    {
        if (preg_match('/\/(?:works|books|authors)\/([^\/]+)/', $key, $matches)) {
            return $matches[1];
        }

        return null;
    }

}
