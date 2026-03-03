<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalConfidentialController extends AbstractController
{
    #[Route('/legal/confidential', name: 'app_legal_confidential')]
    public function index(): Response
    {
        return $this->render('legal_confidential/index.html.twig', [
            'controller_name' => 'LegalConfidentialController',
        ]);
    }
}
