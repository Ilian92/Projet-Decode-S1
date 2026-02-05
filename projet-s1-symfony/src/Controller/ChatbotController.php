<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
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
    public function sendMessage(Request $request): StreamedResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $message = $data['message'] ?? '';
            $threadId = $data['thread_id'] ?? null;

            if ($message == '') {
                throw new \Exception("Un message doit être renseigné");
            }

            $response = $this->httpClient->request('POST', 'http://host.docker.internal:8080/Agent/stream', [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Connection' => 'keep-alive'
                ],
                'body' => json_encode([
                    'message' => $message,
                    'thread_id' => $threadId
                ]),
                'buffer' => false,
                'timeout' => 5000,
            ]);

            return new StreamedResponse(function () use ($response) {
                try {
                    foreach ($this->httpClient->stream($response) as $chunk) {
                        if (!$chunk->isLast()) {
                            $content = $chunk->getContent();
                            echo $content;
                            flush();
                        }
                    }
                    error_log("Streaming terminé avec succès");
                } catch (\Exception $e) {
                    error_log("Erreur pendant le streaming: " . $e->getMessage());
                    echo json_encode(['error' => $e->getMessage()]);
                    flush();
                }
            }, Response::HTTP_OK, [
                'Content-Type' => 'text/event-stream',
                'Cache-Control' => 'no-cache',
                'Connection' => 'keep-alive',
            ]);
        } catch (\Exception $e) {
            error_log("ERREUR globale: " . $e->getMessage());
            error_log("Stack trace: " . $e->getTraceAsString());
            return new StreamedResponse(function () use ($e) {
                echo json_encode(['error' => $e->getMessage()]);
                flush();
            }, Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
