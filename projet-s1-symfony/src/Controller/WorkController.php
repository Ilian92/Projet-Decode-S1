<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\WorkRepository;
use App\Service\OpenLibraryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class WorkController extends AbstractController
{
    private const EDITIONS_LIMIT = 12;

    public function __construct(
        private readonly OpenLibraryService $openLibraryService,
        private readonly WorkRepository $workRepository,
        private readonly BookRepository $bookRepository
    ) {
    }

    #[Route('/work/{id}', name: 'app_work_show', requirements: ['id' => 'OL[0-9A-Z]+W'])]
    public function show(Request $request, string $id): Response
    {
        // Support full key in URL (e.g. /works/OL123W) or OLID only
        $olid = $this->openLibraryService->extractOlid($id) ?? $id;

        try {
            $work = $this->openLibraryService->fetchWork($olid);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException('Œuvre introuvable.');
        }

        if (empty($work) || !isset($work['key'])) {
            throw new NotFoundHttpException('Œuvre introuvable.');
        }

        // Resolve author names (work API only returns author keys)
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

        // Description: can be string or { type, value }
        $description = null;
        if (isset($work['description'])) {
            if (is_string($work['description'])) {
                $description = $work['description'];
            } elseif (is_array($work['description']) && isset($work['description']['value'])) {
                $description = $work['description']['value'];
            }
        }

        // Cover: work has covers[] or first_edition cover
        $coverId = null;
        if (!empty($work['covers'])) {
            $coverId = is_array($work['covers']) ? $work['covers'][0] : $work['covers'];
        } elseif (isset($work['cover_id'])) {
            $coverId = $work['cover_id'];
        }
        $coverUrl = $coverId
            ? "https://covers.openlibrary.org/b/id/{$coverId}-L.jpg"
            : null;

        // First publish date (can be full date or year)
        $firstPublishDate = $work['first_publish_date'] ?? null;
        $firstPublishYear = null;
        if ($firstPublishDate && preg_match('/\d{4}/', $firstPublishDate, $m)) {
            $firstPublishYear = $m[0];
        }

        $subjects = $work['subjects'] ?? [];
        $subjects = is_array($subjects) ? array_slice($subjects, 0, 10) : [];

        // Fetch editions from OpenLibrary API using fetchWorkBooks()
        $books = [];
        $editions = [];
        $workTitle = $work['title'] ?? null;
        
        try {
            // Fetch all editions for this work from OpenLibrary
            $editionsResponse = $this->openLibraryService->fetchWorkBooks($olid, null, 0);
            $entries = $editionsResponse['entries'] ?? [];
            
            // Try to find Work entity in database by title to match with Book entities
            $workEntity = null;
            if ($workTitle) {
                $workEntity = $this->workRepository->findOneBy(['title' => $workTitle]);
            }
            
            // If we found a Work entity, get all its Book entities (editions available for purchase)
            if ($workEntity) {
                $books = $this->bookRepository->findByWork($workEntity);
            }
            
            // Format OpenLibrary editions for display (reference only)
            foreach ($entries as $edition) {
                $editions[] = $this->openLibraryService->formatEditionForFrontend($edition);
            }
        } catch (\Throwable $e) {
            // Optional: show work even without editions
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
                'editions' => $editions,
            ],
            'books' => $books, // Books from database (editions available for purchase)
        ]);
    }
}
