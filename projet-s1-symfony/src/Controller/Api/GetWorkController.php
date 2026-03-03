<?php

namespace App\Controller\Api;

use App\Repository\WorkRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetWorkController extends AbstractController
{
    #[Route('/database/get/work/{id}', name: 'app_get_work', methods: ['GET'])]
    public function index(string $id, WorkRepository $workRepository): Response
    {
        $work = $workRepository->find($id);

        if (!$work) {
            return new JsonResponse(['error' => 'Work not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $work->getId(),
            'title' => $work->getTitle(),
            'summary' => $work->getSummary(),
            'genres' => array_map(
                fn($genre) => [
                    'id' => $genre->getId(),
                    'label' => $genre->getLabel()
                ],
                $work->getGenre()->toArray()
            ),
            'authors' => array_map(
                fn($author) => [
                    'id' => $author->getId(),
                    'firstName' => $author->getFirstName(),
                    'lastName' => $author->getLastName()
                ],
                $work->getAuthor()->toArray()
            ),
            'books' => array_map(
                fn($book) => [
                    'id' => $book->getId(),
                    'publicationDate' => $book->getPublicationDate()?->format('Y-m-d'),
                    'currentUnitPrice' => $book->getCurrentUnitPrice(),
                    'availableStock' => $book->getAvailableStock()
                ],
                $work->getBook()->toArray()
            )
        ];

        return new JsonResponse($data);
    }
}
