<?php

namespace App\Controller\Api;

use App\Service\BookRelatedDBInsertionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class BookRelatedDBInsertionController extends AbstractController
{
    #[Route('/database/insertion', name: 'app_database_insertion', methods: ['POST'])]
    public function index(
        Request $request,
        BookRelatedDBInsertionService $insertionService,
    ): Response {
        try {
            $data = json_decode($request->getContent(), true);

            if (!is_array($data)) {
                return new JsonResponse([
                    'success' => false,
                    'error' => 'Invalid JSON format'
                ], Response::HTTP_BAD_REQUEST);
            }

            $ids = $insertionService->processInsertion($data);

            return new JsonResponse([
                'success' => true,
                'message' => 'Data successfully inserted',
                'ids'     => $ids,
            ], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'error'   => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
