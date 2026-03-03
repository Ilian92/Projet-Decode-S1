<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class LegalSalesController extends AbstractController
{
    #[Route('/legal/sales', name: 'app_legal_sales')]
    public function index(): Response
    {
        return $this->render('legal_sales/index.html.twig', [
            'controller_name' => 'LegalSalesController',
        ]);
    }
}
