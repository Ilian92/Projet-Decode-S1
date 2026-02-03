<?php

namespace App\Controller;

use App\Entity\MonthlyBox;
use App\Form\MonthlyBoxType;
use App\Repository\MonthlyBoxRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/monthly/box')]
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
}
