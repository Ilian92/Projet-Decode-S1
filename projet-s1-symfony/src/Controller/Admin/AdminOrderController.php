<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Enum\OrderStatus;
use App\Form\OrderType;
use App\Repository\OrderRepository;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/order')]
final class AdminOrderController extends AbstractController
{
    #[Route(name: 'app_admin_order_index', methods: ['GET'])]
    public function index(OrderRepository $orderRepository): Response
    {
        return $this->render('admin_order/index.html.twig', [
            'orders' => $orderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_order_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $order = new Order();
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($order);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_order/new.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_show', methods: ['GET'])]
    public function show(Order $order): Response
    {
        return $this->render('admin_order/show.html.twig', [
            'order' => $order,
        ]);
    }

    #[Route('/{id}/ship', name: 'app_admin_order_ship', methods: ['POST'])]
    public function ship(Request $request, Order $order, OrderService $orderService): Response
    {
        if (!$this->isCsrfTokenValid('ship'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($order->getStatus() !== OrderStatus::PENDING_SHIPMENT->value) {
            $this->addFlash('error', 'Seules les commandes en préparation peuvent être expédiées.');
            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }
        if (!$order->isFulfillable()) {
            $this->addFlash('error', 'Impossible d\'expédier : des livres sont manquants (stock insuffisant). Réapprovisionnez les livres concernés.');
            return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($orderService->ship($order)) {
            $this->addFlash('success', 'Commande #'.$order->getId().' marquée comme expédiée.');
        } else {
            $this->addFlash('error', 'Impossible d\'expédier : des livres sont manquants (stock insuffisant).');
        }
        return $this->redirectToRoute('app_admin_order_show', ['id' => $order->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_admin_order_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(OrderType::class, $order);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_order_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_order/edit.html.twig', [
            'order' => $order,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_order_delete', methods: ['POST'])]
    public function delete(Request $request, Order $order, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$order->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($order);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_order_index', [], Response::HTTP_SEE_OTHER);
    }
}
