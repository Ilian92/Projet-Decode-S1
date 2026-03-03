<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalMentionsController extends AbstractController
{
    #[Route('/legal/mentions', name: 'app_legal_mentions')]
    public function index(): Response
    {
        return $this->render('legal_mentions/index.html.twig', [
            'controller_name' => 'LegalMentionsController',
        ]);
    }
}
