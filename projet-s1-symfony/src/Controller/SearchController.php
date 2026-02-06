<?php

namespace App\Controller;

use App\Constant\SubjectFilters;
use App\Service\OpenLibraryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SearchController extends AbstractController
{
    private const RESULTS_PER_PAGE = 24;
    private const SORT_OPTIONS = [
        'relevance' => 'Pertinence',
        'new' => 'Nouveautés',
        'rating' => 'Mieux notés',
        'editions' => 'Plus d\'éditions',
    ];

    public function __construct(
        private readonly OpenLibraryService $openLibraryService
    ) {
    }

    #[Route('/search', name: 'app_search', methods: ['GET'])]
    public function search(Request $request): Response
    {
        $query = trim((string) $request->query->get('q', ''));
        $subject = $request->query->get('subject', '');
        $sort = $request->query->get('sort', 'relevance');
        $page = max(1, (int) $request->query->get('page', 1));

        $results = [];
        $totalResults = 0;
        $hasSearched = $query !== '' || $subject !== '';

        if ($hasSearched) {
            $offset = ($page - 1) * self::RESULTS_PER_PAGE;
            $params = [
                'limit' => self::RESULTS_PER_PAGE,
                'offset' => $offset,
                'fields' => 'key,title,author_name,cover_i,first_publish_year,edition_count',
            ];

            if ($query !== '') {
                $params['q'] = $query;
            }
            if ($subject !== '' && isset(SubjectFilters::FILTERS[$subject])) {
                $params['subject'] = $subject;
            }
            if ($sort === 'new') {
                $params['sort'] = 'first_publish_year desc';
            } elseif ($sort === 'editions') {
                $params['sort'] = 'edition_count desc';
            }
            // relevance and rating: API default

            try {
                $searchResponse = $this->openLibraryService->search($params);
                $totalResults = (int) ($searchResponse['numFound'] ?? 0);
                $docs = $searchResponse['docs'] ?? [];
                foreach ($docs as $work) {
                    $results[] = $this->openLibraryService->formatWorkForFrontend($work);
                }
            } catch (\Throwable $e) {
                $results = [];
                $totalResults = 0;
            }
        }

        $totalPages = $totalResults > 0
            ? (int) ceil($totalResults / self::RESULTS_PER_PAGE)
            : 0;

        return $this->render('search/index.html.twig', [
            'query' => $query,
            'subject' => $subject,
            'sort' => $sort,
            'page' => $page,
            'results' => $results,
            'totalResults' => $totalResults,
            'totalPages' => $totalPages,
            'resultsPerPage' => self::RESULTS_PER_PAGE,
            'subjectFilters' => SubjectFilters::FILTERS,
            'sortOptions' => self::SORT_OPTIONS,
            'hasSearched' => $hasSearched,
        ]);
    }
}
