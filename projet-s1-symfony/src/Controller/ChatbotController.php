<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ChatbotController extends AbstractController
{
    private HttpClientInterface $httpClient;

    public function __construct(HttpClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;
    }

    #[Route('/chat', name: 'app_chat', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }

    #[Route('/api/chatbot/message', name: 'api_chatbot_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $message = $data['message'] ?? '';
            $threadId = $data['thread_id'] ?? null;

            if ($message == '') {
                throw "Un message doit être renseigné";
            }

            $response = $this->httpClient->request('POST', 'http://host.docker.internal:8080/Agent/invoke', [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'body' => json_encode([
                    'message' => $message,
                    'thread_id' => $threadId
                ]),
            ]);

            $content = $response->getContent();

            return new JsonResponse(json_decode($content, true));
        } catch (\Exception $e) {
            error_log("ERREUR: " . $e->getMessage());
            return new JsonResponse([
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
