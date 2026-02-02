<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Service\OpenLibraryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    public function __construct(
        private readonly OpenLibraryService $openLibraryService,
        private readonly BookRepository $bookRepository
    ) {
    }

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        // Optimized: Use search API instead of subject API (much faster)
        // Reduced categories from 5 to 3, books from 6 to 4 per category
        // All requests are cached for 1 hour
        
        $popularSubjects = ['fiction', 'science_fiction', 'mystery'];
        $booksPerCategory = 4;
        
        // Fetch bestsellers from database (limited to 6 for minimal section)
        $bestsellers = [];
        try {
            $bestsellerBooks = $this->bookRepository->findBestsellers(6);
            foreach ($bestsellerBooks as $book) {
                $work = $book->getWork();
                if ($work) {
                    $authors = $work->getAuthor();
                    $authorName = 'Auteur inconnu';
                    if (!$authors->isEmpty()) {
                        $firstAuthor = $authors->first();
                        $authorName = $firstAuthor->getFirstName() . ' ' . $firstAuthor->getLastName();
                    }
                    
                    $price = '24,90';
                    if ($book->getCurrentUnitPrice()) {
                        $priceInEuros = $book->getCurrentUnitPrice() / 100;
                        $price = number_format($priceInEuros, 2, ',', ' ');
                    }
                    
                    $bestsellers[] = [
                        'title' => $work->getTitle(),
                        'author' => $authorName,
                        'cover_url' => $book->getCoverImageUrl(),
                        'price' => $price,
                        'id' => $book->getId(),
                    ];
                }
            }
        } catch (\Exception $e) {
            // Continue with empty array
        }

        try {
            // Optimized search for editor's picks
            $editorsPickResults = $this->openLibraryService->search([
                'q' => 'classic',
                'limit' => 4,
                'fields' => 'key,title,author_name,cover_i' // Minimal fields
            ]);
            if (isset($editorsPickResults['docs'])) {
                foreach ($editorsPickResults['docs'] as $work) {
                    $editorsPicks[] = $this->openLibraryService->formatWorkForFrontend($work);
                }
            }
        } catch (\Exception $e) {
            // Continue with empty array
        }

        // Use search API for categories (much faster than subject API with details)
        foreach ($popularSubjects as $subject) {
            try {
                // Use search API instead of subject API - much faster!
                $searchResults = $this->openLibraryService->searchBySubject($subject, $booksPerCategory, 0);
                
                $books = [];
                if (isset($searchResults['docs'])) {
                    foreach ($searchResults['docs'] as $work) {
                        $books[] = $this->openLibraryService->formatWorkForFrontend($work);
                    }
                }
                
                // Use search result count directly (no extra API call needed)
                $workCount = $searchResults['numFound'] ?? 0;
                
                $categories[] = [
                    'name' => ucfirst(str_replace('_', ' ', $subject)),
                    'key' => $subject,
                    'work_count' => $workCount,
                    'subcategories' => [], // Skip subcategories for performance
                    'books' => $books,
                ];
            } catch (\Exception $e) {
                // Skip this category if there's an error
                continue;
            }
        }

        return $this->render('home/index.html.twig', [
            'bestsellers' => $bestsellers,
            'editorsPicks' => $editorsPicks,
            'categories' => $categories,
        ]);
    }
}
