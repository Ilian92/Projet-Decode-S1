<?php

namespace App\Service;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Genre;
use App\Entity\Work;
use App\Enum\ImageSize;
use App\Enum\ImageType;
use App\Enum\OrderStatus;
use App\Repository\AuthorRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use App\Repository\OrderRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookService
{
    private const DEFAULT_UNIT_PRICE_CENTIMES = 999;

    public function __construct(
        private OrderRepository $orderRepository,
        private EntityManagerInterface $entityManager,
        private BookRepository $bookRepository,
        private WorkRepository $workRepository,
        private AuthorRepository $authorRepository,
        private GenreRepository $genreRepository,
        private OpenLibraryService $openLibraryService
    ) {
    }

    public function restock(Book $book, int $newQuantity): void
    {
        $book->setAvailableStock($newQuantity);
        $this->entityManager->flush();
        $this->syncOrdersWithBookStock($book);
    }

    public function syncOrdersWithBookStock(Book $book): int
    {
        $availableStock = $book->getAvailableStock() ?? 0;
        $updated = 0;

        if ($availableStock === 0) {
            foreach ($this->orderRepository->findByBookStatus($book, OrderStatus::PENDING_SHIPMENT) as $order) {
                $order->setStatus(OrderStatus::PENDING_RESTOCK->value);
                $this->entityManager->flush();
                ++$updated;
            }
        } else {
            foreach ($this->orderRepository->findByBookStatus($book, OrderStatus::PENDING_RESTOCK) as $order) {
                $order->setStatus(OrderStatus::PENDING_SHIPMENT->value);
                $this->entityManager->flush();
                ++$updated;
            }
        }

        return $updated;
    }

    public function createFromOlid(string $olid): ?Book
    {
        // If the book already exists locally, just return it
        $existing = $this->bookRepository->find($olid);
        if ($existing instanceof Book) {
            return $existing;
        }

        try {
            $bookData = $this->openLibraryService->fetchBook($olid);
        } catch (\Throwable $e) {
            return null;
        }

        if (!isset($bookData['works'][0]['key'])) {
            return null;
        }

        $workKey = (string) $bookData['works'][0]['key'];
        $workId = $this->openLibraryService->extractOlid($workKey);
        if ($workId === null) {
            return null;
        }

        // Find or create the Work entity
        $work = $this->workRepository->find($workId);
        $workData = null;

        if (!$work instanceof Work) {
            try {
                $workData = $this->openLibraryService->fetchWork($workId);
            } catch (\Throwable $e) {
                return null;
            }

            $work = new Work();
            $work->setId($workId);
            $work->setTitle($workData['title'] ?? ($bookData['title'] ?? 'Untitled'));

            $summary = null;
            if (isset($workData['description'])) {
                $summary = is_array($workData['description'])
                    ? ($workData['description']['value'] ?? null)
                    : $workData['description'];
            }

            if ($summary !== null) {
                $work->setSummary($summary);
            }

            $this->entityManager->persist($work);
        }

        // Ensure we have work data available for genres/authors if not already fetched
        if ($workData === null) {
            try {
                $workData = $this->openLibraryService->fetchWork($workId);
            } catch (\Throwable $e) {
                $workData = [];
            }
        }

        // Handle authors (linked to the Work)
        $authorRefs = $bookData['authors'] ?? ($workData['authors'] ?? []);

        foreach ($authorRefs as $authorRef) {
            $authorKey = null;
            if (is_array($authorRef)) {
                $authorKey = $authorRef['key'] ?? null;
            } elseif (\is_string($authorRef)) {
                $authorKey = $authorRef;
            }

            if ($authorKey === null) {
                continue;
            }

            $authorId = $this->openLibraryService->extractOlid($authorKey);
            if ($authorId === null) {
                continue;
            }

            $authorData = [];
            try {
                $authorData = $this->openLibraryService->fetchAuthor($authorId);
            } catch (\Throwable $e) {
                // Skip this author on failure
                continue;
            }

            $fullName = $authorData['personal_name'] ?? $authorData['name'] ?? null;
            if ($fullName === null || $fullName === '') {
                continue;
            }

            $parts = preg_split('/\s+/', $fullName);
            $lastName = array_pop($parts);
            $firstName = \count($parts) > 0 ? implode(' ', $parts) : '';

            $author = $this->authorRepository->findOneBy([
                'firstName' => $firstName,
                'lastName' => $lastName,
            ]);

            if (!$author instanceof Author) {
                $author = new Author();
                if ($firstName !== '') {
                    $author->setFirstName($firstName);
                }
                $author->setLastName($lastName);

                if (isset($authorData['bio'])) {
                    $bio = is_array($authorData['bio'])
                        ? ($authorData['bio']['value'] ?? null)
                        : $authorData['bio'];
                    if ($bio !== null) {
                        $author->setBiography($bio);
                    }
                }

                if (isset($authorData['photos'][0])) {
                    $photoId = (string) $authorId;
                    $photoUrl = $this->openLibraryService->getImageUrl(
                        $photoId,
                        ImageType::AUTHOR,
                        ImageSize::MEDIUM
                    );
                    $author->setPhotoUrl($photoUrl);
                }

                $this->entityManager->persist($author);
            }

            $work->addAuthor($author);
        }

        // Handle genres (subjects on the work)
        $subjects = $workData['subjects'] ?? [];
        if (\is_array($subjects)) {
            foreach ($subjects as $subject) {
                if (!\is_string($subject) || $subject === '') {
                    continue;
                }

                $genre = $this->genreRepository->findOneBy(['label' => $subject]);
                if (!$genre instanceof Genre) {
                    $genre = new Genre();
                    $genre->setLabel($subject);
                    $this->entityManager->persist($genre);
                }

                $work->addGenre($genre);
            }
        }

        // Finally, create the Book entity itself
        $book = new Book();
        $book->setId($olid);
        $book->setWork($work);

        $publishDateString = $bookData['publish_date'] ?? null;
        try {
            $publicationDate = $publishDateString !== null
                ? new \DateTime($publishDateString)
                : new \DateTime();
        } catch (\Exception) {
            $publicationDate = new \DateTime();
        }

        $book->setPublicationDate($publicationDate);
        $book->setReleaseDate($publicationDate);

        $book->setAvailableStock(0);
        $book->setCurrentUnitPrice(self::DEFAULT_UNIT_PRICE_CENTIMES);

        $weightGrams = $this->parseWeightToGrams($bookData['weight'] ?? null);
        if ($weightGrams !== null) {
            $book->setWeightGrams($weightGrams);
        }

        // Optional fields
        if (isset($bookData['covers'][0])) {
            $coverId = (string) $bookData['covers'][0];
            $coverUrl = $this->openLibraryService->getImageUrl(
                $coverId,
                ImageType::BOOK,
                ImageSize::MEDIUM
            );
            $book->setCoverImageUrl($coverUrl);
        }

        $this->entityManager->persist($book);
        $this->entityManager->flush();

        return $book;
    }

    private function parseWeightToGrams(mixed $weight): ?int
    {
        if ($weight === null || $weight === '') {
            return null;
        }
        $weight = trim((string) $weight);
        if ($weight === '') {
            return null;
        }
        if (preg_match('/^([\d.]+)\s*(g|gram|grams)\s*$/i', $weight, $m)) {
            return (int) round((float) $m[1]);
        }
        if (preg_match('/^([\d.]+)\s*(oz|ounce|ounces)\s*$/i', $weight, $m)) {
            return (int) round((float) $m[1] * 28.3495);
        }
        if (preg_match('/^([\d.]+)\s*(lb|pound|pounds)\s*$/i', $weight, $m)) {
            return (int) round((float) $m[1] * 453.592);
        }
        if (preg_match('/^([\d.]+)\s*(kg|kilogram|kilograms)\s*$/i', $weight, $m)) {
            return (int) round((float) $m[1] * 1000);
        }

        return null;
    }
}
