<?php

namespace App\Controller\Pages\Legal;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalCookieController extends AbstractController
{
    #[Route('/legal/cookie', name: 'app_legal_cookie')]
    public function index(): Response
    {
        return $this->render('legal_cookie/index.html.twig', [
            'controller_name' => 'LegalCookieController',
        ]);
    }
}
