<?php

namespace App\Controller\Pages;

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
                        'work_id' => $work->getId(),
                    ];
                }
            }
        } catch (\Exception $e) {

        }

        $editorsPicks = [];
        try {
            $editorsPickResults = $this->openLibraryService->search([
                'q' => 'classic',
                'limit' => 4,
            ]);
            if (isset($editorsPickResults['docs'])) {
                foreach ($editorsPickResults['docs'] as $work) {
                    $editorsPicks[] = $this->openLibraryService->formatWorkForFrontend($work);
                }
            }
        } catch (\Exception $e) {

        }

        $categories = [];
        $popularSubjects = ['fiction', 'science_fiction', 'mystery'];
        foreach ($popularSubjects as $subject) {
            try {
                $subjectResults = $this->openLibraryService->searchSubject($subject, 4, 0);

                $books = [];
                if (isset($subjectResults['works']) && is_array($subjectResults['works'])) {
                    foreach ($subjectResults['works'] as $work) {
                        $books[] = $this->openLibraryService->formatWorkForFrontend($work);
                    }
                }

                $workCount = $subjectResults['work_count'] ?? 0;

                $categories[] = [
                    'name' => ucfirst(str_replace('_', ' ', $subject)),
                    'key' => $subject,
                    'work_count' => $workCount,
                    'books' => $books,
                ];
            } catch (\Exception $e) {

            }
        }

        return $this->render('home/index.html.twig', [
            'bestsellers' => $bestsellers,
            'editorsPicks' => $editorsPicks,
            'categories' => $categories,
        ]);
    }
}
