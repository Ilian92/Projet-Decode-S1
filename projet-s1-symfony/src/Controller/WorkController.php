<?php

namespace App\Controller;

use App\Entity\Work;
use App\Enum\ImageSize;
use App\Enum\ImageType;
use App\Repository\BookRepository;
use App\Repository\WorkRepository;
use App\Service\OpenLibraryService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WorkController extends AbstractController
{
    public function __construct(
        private readonly OpenLibraryService $openLibraryService,
        private readonly WorkRepository $workRepository,
        private readonly BookRepository $bookRepository,
        private readonly EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/work/{id}', name: 'app_work_show', requirements: ['id' => 'OL[0-9A-Z]+W'])]
    public function show(Request $request, string $id): Response
    {
        $olid = $this->openLibraryService->extractOlid($id) ?? $id;

        try {
            $work = $this->openLibraryService->fetchWork($olid);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException('Œuvre introuvable.');
        }

        if (empty($work) || !isset($work['key'])) {
            throw new NotFoundHttpException('Œuvre introuvable.');
        }

        $authors = [];
        $authorKeys = $work['authors'] ?? [];
        foreach ($authorKeys as $authorRef) {
            $authorKey = is_array($authorRef) ? ($authorRef['author']['key'] ?? null) : null;
            if (!$authorKey) {
                continue;
            }
            $authorOlid = $this->openLibraryService->extractOlid($authorKey);
            if (!$authorOlid) {
                continue;
            }
            try {
                $authorData = $this->openLibraryService->fetchAuthor($authorOlid);
                $authors[] = [
                    'key' => $authorKey,
                    'name' => $authorData['name'] ?? 'Auteur inconnu',
                ];
            } catch (\Throwable $e) {
                $authors[] = ['key' => $authorKey, 'name' => 'Auteur inconnu'];
            }
        }
        if (empty($authors)) {
            $authors = [['key' => null, 'name' => 'Auteur inconnu']];
        }

        $description = null;
        if (isset($work['description'])) {
            if (is_string($work['description'])) {
                $description = $work['description'];
            } elseif (is_array($work['description']) && isset($work['description']['value'])) {
                $description = $work['description']['value'];
            }
        }

        $coverId = null;
        if (!empty($work['covers'])) {
            $coverId = is_array($work['covers']) ? $work['covers'][0] : $work['covers'];
        } elseif (isset($work['cover_id'])) {
            $coverId = $work['cover_id'];
        }
        $coverUrl = $this->openLibraryService->getImageUrl($coverId, ImageType::BOOK, ImageSize::LARGE);

        $firstPublishDate = $work['first_publish_date'] ?? null;
        $firstPublishYear = null;
        if ($firstPublishDate && preg_match('/\d{4}/', $firstPublishDate, $m)) {
            $firstPublishYear = $m[0];
        }

        $subjects = $work['subjects'] ?? [];
        $subjects = is_array($subjects) ? array_slice($subjects, 0, 10) : [];

        $workTitle = $work['title'] ?? 'Sans titre';
        $workEntity = $this->workRepository->find($olid);
        if (!$workEntity) {
            $workEntity = new Work();
            $workEntity->setId($olid);
            $workEntity->setTitle($workTitle);
            if (isset($description) && $description !== null) {
                $workEntity->setSummary($description);
            }
            $this->entityManager->persist($workEntity);
            $this->entityManager->flush();
        }
        $workOlid = $workEntity->getId();

        $ratings = null;
        try {
            $ratingsData = $this->openLibraryService->fetchWorkRatings($olid);
            if (isset($ratingsData['summary'])) {
                $ratings = [
                    'average' => $ratingsData['summary']['average'] ?? null,
                    'count' => $ratingsData['summary']['count'] ?? 0,
                ];
            }
        } catch (\Throwable $e) {
            // Ratings not available, continue without them
        }

        $editions = [];
        try {
            $editionsResponse = $this->openLibraryService->fetchWorkBooks($olid, null, 0);
            $entries = $editionsResponse['entries'] ?? [];

            $books = [];
            if ($workEntity) {
                $books = $this->bookRepository->findBy(['work' => $workEntity], ['publicationDate' => 'DESC', 'currentUnitPrice' => 'ASC']);
            }

            $booksByEditionOlid = [];
            foreach ($books as $book) {
                $booksByEditionOlid[$book->getId()] = $book;
            }

            foreach ($entries as $edition) {
                $formattedEdition = $this->openLibraryService->formatEditionForFrontend($edition);
                $editionKey = $formattedEdition['key'] ?? null;
                $bookOlid = $editionKey !== null ? $this->openLibraryService->extractOlid($editionKey) : null;

                $bookForEdition = $bookOlid !== null ? ($booksByEditionOlid[$bookOlid] ?? null) : null;
                $isAvailable = $bookForEdition !== null && (($bookForEdition->getAvailableStock() ?? 0) > 0);
                $bookId = $bookForEdition?->getId();

                $formattedEdition['book_olid'] = $bookOlid;
                $formattedEdition['is_available'] = $isAvailable;
                $formattedEdition['book_id'] = $bookId;

                $editions[] = $formattedEdition;
            }
        } catch (\Throwable $e) {
            // Editions not available, continue without them
        }

        return $this->render('work/show.html.twig', [
            'work' => [
                'key' => $work['key'],
                'olid' => $olid,
                'title' => $work['title'] ?? 'Sans titre',
                'authors' => $authors,
                'description' => $description,
                'cover_url' => $coverUrl,
                'first_publish_date' => $firstPublishDate,
                'first_publish_year' => $firstPublishYear,
                'subjects' => $subjects,
                'ratings' => $ratings,
            ],
            'editions' => $editions,
            'workOlid' => $workOlid,
        ]);
    }
}
