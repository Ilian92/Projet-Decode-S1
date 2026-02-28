<?php

namespace App\Controller;

use App\Entity\Work;
use App\Form\WorkType;
use App\Repository\WorkRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/work')]
final class AdminWorkController extends AbstractController
{
    #[Route(name: 'app_admin_work_index', methods: ['GET'])]
    public function index(WorkRepository $workRepository): Response
    {
        return $this->render('admin_work/index.html.twig', [
            'works' => $workRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_work_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $work = new Work();
        $form = $this->createForm(WorkType::class, $work);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($work);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_work_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_work/new.html.twig', [
            'work' => $work,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_work_show', methods: ['GET'])]
    public function show(Work $work): Response
    {
        return $this->render('admin_work/show.html.twig', [
            'work' => $work,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_work_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Work $work, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(WorkType::class, $work, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_work_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_work/edit.html.twig', [
            'work' => $work,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_work_delete', methods: ['POST'])]
    public function delete(Request $request, Work $work, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$work->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($work);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_work_index', [], Response::HTTP_SEE_OTHER);
    }
}
