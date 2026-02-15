<?php

namespace App\Controller;

use App\Enum\ImageSize;
use App\Enum\ImageType;
use App\Repository\AuthorRepository;
use App\Service\OpenLibraryService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class AuthorController extends AbstractController
{
    public function __construct(
        private readonly OpenLibraryService $openLibraryService,
        private readonly AuthorRepository $authorRepository
    ) {
    }

    #[Route('/author/{id}', name: 'app_author', requirements: ['id' => 'OL[0-9A-Z]+A'])]
    public function show(string $id): Response
    {
        $olid = $this->openLibraryService->extractOlid($id) ?? $id;

        try {
            $authorData = $this->openLibraryService->fetchAuthor($olid);
        } catch (\Throwable $e) {
            throw new NotFoundHttpException('Auteur introuvable.');
        }

        if (empty($authorData) || !isset($authorData['key'])) {
            throw new NotFoundHttpException('Auteur introuvable.');
        }

        $name = $authorData['name'] ?? 'Auteur inconnu';

        $biography = null;
        if (isset($authorData['bio'])) {
            if (is_string($authorData['bio'])) {
                $biography = $authorData['bio'];
            } elseif (is_array($authorData['bio']) && isset($authorData['bio']['value'])) {
                $biography = $authorData['bio']['value'];
            }
        }

        $photoUrl = null;
        if (isset($authorData['photos']) && !empty($authorData['photos'])) {
            $photoId = is_array($authorData['photos']) ? $authorData['photos'][0] : $authorData['photos'];
            $photoUrl = $this->openLibraryService->getImageUrl($olid, ImageType::AUTHOR, ImageSize::LARGE);
        }

        $birthDate = $authorData['birth_date'] ?? null;
        $deathDate = $authorData['death_date'] ?? null;

        $works = [];
        try {
            $worksResponse = $this->openLibraryService->search([
                'author' => $olid,
                'limit' => 50,
            ]);

            if (isset($worksResponse['docs'])) {
                foreach ($worksResponse['docs'] as $workDoc) {
                    $formattedWork = $this->openLibraryService->formatWorkForFrontend($workDoc);
                    $works[] = $formattedWork;
                }
            }
        } catch (\Throwable $e) {
            // Works pas dispo, continue
        }

        $authorEntity = $this->authorRepository->findOneBy([
            'firstName' => explode(' ', $name)[0] ?? $name,
            'lastName' => explode(' ', $name, 2)[1] ?? '',
        ]);

        return $this->render('author/show.html.twig', [
            'author' => [
                'key' => $authorData['key'],
                'olid' => $olid,
                'name' => $name,
                'biography' => $biography,
                'photo_url' => $photoUrl,
                'birth_date' => $birthDate,
                'death_date' => $deathDate,
            ],
            'works' => $works,
            'authorEntity' => $authorEntity,
        ]);
    }
}
