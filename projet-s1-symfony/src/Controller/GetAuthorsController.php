<?php

namespace App\Controller;

use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetAuthorsController extends AbstractController
{
    #[Route('/database/get/authors', name: 'app_get_authors', methods: ['GET'])]
    public function index(AuthorRepository $authorRepository): Response
    {
        $authors = $authorRepository->findAll();

        $data = [];
        foreach ($authors as $author) {
            $data[] = [
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
        }

        return new JsonResponse($data);
    }
}
