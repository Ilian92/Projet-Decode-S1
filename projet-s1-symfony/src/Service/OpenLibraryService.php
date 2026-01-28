<?php

namespace App\Service;

use App\Enum\ImageSize;
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
        // Create cache key from endpoint and query
        $cacheKey = 'openlibrary_' . md5($endpoint . serialize($query));
        
        // Try to get from cache first
        $cacheItem = $this->cache->getItem($cacheKey);
        if ($cacheItem->isHit()) {
            return $cacheItem->get();
        }

        try {
            $response = $this->client->request('GET', $this->apiBaseUrl . $endpoint, [
                'query' => $query,
                'timeout' => 5 // 5 second timeout
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

            // Cache the result
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

    /**
     * Fetch a subject (category) and its works
     * 
     * @param string $subject The subject name (e.g., "love", "fiction", "science_fiction")
     * @param array $options Options: details (bool), ebooks (bool), published_in (string), limit (int), offset (int)
     * @return array Subject data with works
     */
    public function fetchSubject(string $subject, array $options = []): array
    {
        $query = [];
        
        if (isset($options['details']) && $options['details']) {
            $query['details'] = 'true';
        }
        
        if (isset($options['ebooks']) && $options['ebooks']) {
            $query['ebooks'] = 'true';
        }
        
        if (isset($options['published_in'])) {
            $query['published_in'] = $options['published_in'];
        }
        
        if (isset($options['limit'])) {
            $query['limit'] = $options['limit'];
        }
        
        if (isset($options['offset'])) {
            $query['offset'] = $options['offset'];
        }
        
        return $this->getJson("/subjects/$subject.json", $query);
    }

    /**
     * Fetch subcategories (related subjects) for a given subject
     * 
     * @param string $subject The subject name
     * @return array Array of related subjects
     */
    public function fetchSubcategories(string $subject): array
    {
        $data = $this->fetchSubject($subject, ['details' => true]);
        
        return $data['subjects'] ?? [];
    }

    /**
     * Search for books/works
     * 
     * @param array $params Search parameters: q, title, author, subject, fields, sort, lang, limit, offset, page
     * @return array Search results
     */
    public function search(array $params = []): array
    {
        return $this->getJson("/search.json", $params);
    }

    /**
     * Search for works by subject (optimized with minimal fields)
     * 
     * @param string $subject Subject name
     * @param int $limit Number of results
     * @param int $offset Starting offset
     * @return array Search results
     */
    public function searchBySubject(string $subject, int $limit = 20, int $offset = 0): array
    {
        return $this->search([
            'subject' => $subject,
            'limit' => $limit,
            'offset' => $offset,
            'fields' => 'key,title,author_name,cover_i' // Minimal fields for speed
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
        // Handle cover - can be cover_i (search API) or covers array (subject API)
        $coverId = $work['cover_i'] ?? null;
        if (!$coverId && isset($work['covers']) && !empty($work['covers'])) {
            $coverId = is_array($work['covers']) ? $work['covers'][0] : $work['covers'];
        }
        
        $coverUrl = null;
        if ($coverId) {
            $coverUrl = "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg";
        }
        
        // Handle authors - can be author_key/author_name arrays (search API) or authors array (subject API)
        $authorKey = null;
        $authorName = 'Unknown Author';
        
        if (isset($work['author_key']) && isset($work['author_name'])) {
            // Search API format
            $authorKey = $work['author_key'][0] ?? null;
            $authorName = $work['author_name'][0] ?? 'Unknown Author';
        } elseif (isset($work['authors']) && is_array($work['authors']) && !empty($work['authors'])) {
            // Subject API format
            $firstAuthor = $work['authors'][0];
            if (is_array($firstAuthor)) {
                $authorName = $firstAuthor['name'] ?? 'Unknown Author';
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
            $coverUrl = "https://covers.openlibrary.org/b/id/{$coverId}-M.jpg";
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

    /**
     * Get cover image URL by cover ID
     * 
     * @param int|null $coverId Cover ID from OpenLibrary
     * @param ImageSize $size Image size
     * @return string|null Cover image URL or null
     */
    public function getCoverUrl(?int $coverId, ImageSize $size = ImageSize::MEDIUM): ?string
    {
        if (!$coverId) {
            return null;
        }
        
        $s = match ($size) {
            ImageSize::SMALL => "S",
            ImageSize::LARGE => "L",
            default => "M",
        };
        
        return "https://covers.openlibrary.org/b/id/{$coverId}-{$s}.jpg";
    }

    /**
     * Extract OLID (Open Library ID) from a key
     * 
     * @param string $key OpenLibrary key (e.g., "/works/OL123W" or "/books/OL456M")
     * @return string|null OLID (e.g., "OL123W" or "OL456M")
     */
    public function extractOlid(string $key): ?string
    {
        if (preg_match('/\/(?:works|books|authors)\/([^\/]+)/', $key, $matches)) {
            return $matches[1];
        }
        
        return null;
    }

}
