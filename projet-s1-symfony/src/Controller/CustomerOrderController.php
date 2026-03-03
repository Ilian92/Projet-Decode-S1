<?php

namespace App\Controller;

use App\Entity\Customer;
use App\Entity\Order;
use App\Repository\OrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/profile/orders', name: 'app_order_')]
final class CustomerOrderController extends AbstractController
{
    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'], methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function show(Order $order): Response
    {
        $user = $this->getUser();
        if (!$user instanceof Customer || $order->getCustomer() !== $user) {
            throw $this->createNotFoundException('Commande introuvable.');
        }

        return $this->render('order/show.html.twig', [
            'order' => $order,
            'customer' => $user,
        ]);
    }
}

