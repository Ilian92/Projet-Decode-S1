<?php

namespace App\Controller\Api;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookPublisher;
use App\Entity\Genre;
use App\Entity\Work;
use App\Repository\AuthorRepository;
use App\Repository\BookPublisherRepository;
use App\Repository\BookRepository;
use App\Repository\GenreRepository;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

final class BookRelatedDBInsertionController extends AbstractController
{
    /*
    * @Route("/database/insertion", name="app_database_insertion", methods={"POST"})
    * Handles the insertion of authors, genres, works, book publishers, and books into the database.
    * input JSON structure:
    * {
    *   "authors": [ {author1_data}, {author2_data}, ... ],
    *   "genres": [ {genre1_data}, {genre2_data}, ... ],
    *   "work": {work_data, "authorIds": [id1, id2, ...], "genreIds": [id1, id2, ...]},
    *   "bookPublisher": {book_publisher_data},
    *   "book": {book_data}
    * }
    */
    #[Route('/database/insertion', name: 'app_database_insertion', methods: ['POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        AuthorRepository $authorRepository,
        GenreRepository $genreRepository,
        WorkRepository $workRepository,
        BookPublisherRepository $bookPublisherRepository,
        BookRepository $bookRepository
    ): Response {
        try {
            $data = json_decode($request->getContent(), true);

            if (!$data) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON format'
                ], Response::HTTP_BAD_REQUEST);
            }

            // Author - vérifier si l'auteur existe déjà
            $authors = [];
            if (isset($data['authors']) && is_array($data['authors'])) {
                foreach ($data['authors'] as $authorData) {
                    $existingAuthor = $authorRepository->findOneBy([
                        'firstName' => $authorData['firstName'],
                        'lastName' => $authorData['lastName']
                    ]);

                    if ($existingAuthor) {
                        $authors[] = $existingAuthor;
                    } else {
                        $author = $serializer->deserialize(
                            json_encode($authorData),
                            Author::class,
                            'json'
                        );

                        $entityManager->persist($author);
                        $authors[] = $author;
                    }
                }
            }

            // Genre
            $genres = [];
            if (isset($data['genres']) && is_array($data['genres'])) {
                foreach ($data['genres'] as $genreData) {
                    $existingGenre = $genreRepository->findOneBy([
                        'label' => $genreData['label']
                    ]);

                    if ($existingGenre) {
                        $genres[] = $existingGenre;
                    } else {
                        $genre = $serializer->deserialize(
                            json_encode($genreData),
                            Genre::class,
                            'json'
                        );

                        $entityManager->persist($genre);
                        $genres[] = $genre;
                    }
                }
            }

            // BookPublisher
            $bookPublisher = null;
            if (isset($data['bookPublisher'])) {
                $existingPublisher = $bookPublisherRepository->findOneBy([
                    'publisherName' => $data['bookPublisher']['publisherName']
                ]);

                if ($existingPublisher) {
                    $bookPublisher = $existingPublisher;
                } else {
                    $bookPublisher = $serializer->deserialize(
                        json_encode($data['bookPublisher']),
                        BookPublisher::class,
                        'json'
                    );

                    $entityManager->persist($bookPublisher);
                }
            }

            // Work and relations
            $work = null;
            if (isset($data['work'])) {
                $authorIds = $data['work']['authorIds'] ?? [];
                $genreIds = $data['work']['genreIds'] ?? [];

                $existingWork = $workRepository->findOneBy([
                    'title' => $data['work']['title']
                ]);

                if ($existingWork) {
                    $work = $existingWork;
                } else {
                    unset($data['work']['authorIds'], $data['work']['genreIds']);

                    $work = $serializer->deserialize(
                        json_encode($data['work']),
                        Work::class,
                        'json'
                    );

                    foreach ($authorIds as $authorId) {
                        if (isset($authors[$authorId])) {
                            $work->addAuthor($authors[$authorId]);
                        }
                    }

                    foreach ($genreIds as $genreId) {
                        if (isset($genres[$genreId])) {
                            $work->addGenre($genres[$genreId]);
                        }
                    }

                    $entityManager->persist($work);
                }
            }

            // Book and relations
            $book = null;
            if (isset($data['book']) && $work) {
                $bookData = $data['book'];
                $publicationDate = isset($bookData['publicationDate'])
                    ? new \DateTime($bookData['publicationDate'])
                    : null;

                $existingBook = null;
                if ($publicationDate) {
                    $existingBook = $bookRepository->findOneBy([
                        'work' => $work,
                        'publicationDate' => $publicationDate
                    ]);
                }

                if ($existingBook) {
                    $book = $existingBook;
                } else {
                    $book = $serializer->deserialize(
                        json_encode($data['book']),
                        Book::class,
                        'json'
                    );

                    $book->setWork($work);

                    if ($bookPublisher) {
                        $book->setBookPublisher($bookPublisher);
                    }

                    $entityManager->persist($book);
                }
            }

            // Save all to database
            $entityManager->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Data successfully inserted',
                'ids' => [
                    'authors' => array_map(fn($a) => $a->getId(), $authors),
                    'genres' => array_map(fn($g) => $g->getId(), $genres),
                    'work' => $work?->getId(),
                    'bookPublisher' => $bookPublisher?->getId(),
                    'book' => $book?->getId(),
                ],
                'reused' => [
                    'authors' => count(array_filter($authors, fn($a) => !$entityManager->contains($a) || $a->getId() !== null)),
                    'genres' => count(array_filter($genres, fn($g) => !$entityManager->contains($g) || $g->getId() !== null)),
                ]
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
