<?php

namespace App\Controller\Api;

use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetAuthorController extends AbstractController
{
    #[Route('/database/get/author/{id}', name: 'app_get_author', methods: ['GET'])]
    public function index(int $id, AuthorRepository $authorRepository): Response
    {
        $author = $authorRepository->find($id);

        if (!$author) {
            return new JsonResponse(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $data = [
            'id' => $author->getId(),
            'firstName' => $author->getFirstName(),
            'lastName' => $author->getLastName(),
            'biography' => $author->getBiography(),
            'photoUrl' => $author->getPhotoUrl(),
            'works' => array_map(
                fn($work) => [
                    'id' => $work->getId(),
                    'title' => $work->getTitle()
                ],
                $author->getWorks()->toArray()
            )
        ];

        return new JsonResponse($data);
    }
}
