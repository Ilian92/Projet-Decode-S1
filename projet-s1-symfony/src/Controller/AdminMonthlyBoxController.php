<?php

namespace App\Controller;

use App\Entity\MonthlyBox;
use App\Form\MonthlyBoxType;
use App\Repository\MonthlyBoxRepository;
use App\Repository\SubscriptionRepository;
use App\Service\BookService;
use App\Service\MonthlyBoxService;
use App\Service\OrderService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/monthly-box')]
final class AdminMonthlyBoxController extends AbstractController
{
    #[Route(name: 'app_admin_monthly_box_index', methods: ['GET'])]
    public function index(MonthlyBoxRepository $monthlyBoxRepository): Response
    {
        return $this->render('admin_monthly_box/index.html.twig', [
            'monthly_boxes' => $monthlyBoxRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_monthly_box_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $monthlyBox = new MonthlyBox();
        $form = $this->createForm(MonthlyBoxType::class, $monthlyBox);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($monthlyBox);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_monthly_box_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_monthly_box/new.html.twig', [
            'monthly_box' => $monthlyBox,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_monthly_box_show', methods: ['GET'])]
    public function show(MonthlyBox $monthlyBox): Response
    {
        return $this->render('admin_monthly_box/show.html.twig', [
            'monthly_box' => $monthlyBox,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_monthly_box_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, MonthlyBox $monthlyBox, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MonthlyBoxType::class, $monthlyBox);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_monthly_box_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_monthly_box/edit.html.twig', [
            'monthly_box' => $monthlyBox,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_monthly_box_delete', methods: ['POST'])]
    public function delete(Request $request, MonthlyBox $monthlyBox, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$monthlyBox->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($monthlyBox);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_monthly_box_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Exemple request body:
     * {
     *   "subscription_id": 5,
     *   "book_olid": "OL7353617M"
     * }
     */
    #[Route('/api/create', name: 'app_admin_monthly_box_create', methods: ['POST'])]
    public function apiCreate(
        Request $request,
        MonthlyBoxService $monthlyBoxService,
        SubscriptionRepository $subscriptionRepository,
        BookService $bookService,
        OrderService $orderService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        if (
            !is_array($data)
            || empty($data['subscription_id'] ?? null)
            || empty($data['book_olid'] ?? null)
        ) {
            return new JsonResponse(
                ['error' => 'Missing required fields: subscription_id and/or book_olid'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $subscription = $subscriptionRepository->find((int) $data['subscription_id']);
        if ($subscription === null) {
            return new JsonResponse(
                ['error' => 'Subscription not found'],
                Response::HTTP_NOT_FOUND
            );
        }

        $customer = $subscription->getCustomer();
        if ($customer === null) {
            return new JsonResponse(
                ['error' => 'Subscription has no associated customer'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $bookOlid = (string) $data['book_olid'];
        $book = $bookService->createFromOlid($bookOlid);
        if ($book === null) {
            return new JsonResponse(
                ['error' => 'Book not found for given OLID'],
                Response::HTTP_NOT_FOUND
            );
        }

        $order = $orderService->create($customer, [
            ['book' => $book, 'quantity' => 1],
        ]);
        if ($order === null) {
            return new JsonResponse(
                ['error' => 'Unable to create order for this subscription'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $referenceMonth = (new \DateTimeImmutable())->format('Y-m');

        $monthlyBox = $monthlyBoxService->create($subscription, $referenceMonth, $order);

        return new JsonResponse([
            'id' => $monthlyBox->getId(),
            'reference_month' => $monthlyBox->getReferenceMonth(),
            'creation_date' => $monthlyBox->getCreationDate()?->format(\DateTimeInterface::ATOM),
            'subscription_id' => $subscription->getId(),
            'order_id' => $order?->getId(),
            'book_olid' => $book->getId(),
        ], Response::HTTP_CREATED);
    }
}
