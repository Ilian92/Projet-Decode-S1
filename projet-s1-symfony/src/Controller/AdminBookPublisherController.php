<?php

namespace App\Controller;

use App\Entity\BookPublisher;
use App\Form\BookPublisherType;
use App\Repository\BookPublisherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/book/publisher')]
final class AdminBookPublisherController extends AbstractController
{
    #[Route(name: 'app_admin_book_publisher_index', methods: ['GET'])]
    public function index(BookPublisherRepository $bookPublisherRepository): Response
    {
        return $this->render('admin_book_publisher/index.html.twig', [
            'book_publishers' => $bookPublisherRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_book_publisher_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $bookPublisher = new BookPublisher();
        $form = $this->createForm(BookPublisherType::class, $bookPublisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($bookPublisher);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_book_publisher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_book_publisher/new.html.twig', [
            'book_publisher' => $bookPublisher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_publisher_show', methods: ['GET'])]
    public function show(BookPublisher $bookPublisher): Response
    {
        return $this->render('admin_book_publisher/show.html.twig', [
            'book_publisher' => $bookPublisher,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_book_publisher_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BookPublisher $bookPublisher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(BookPublisherType::class, $bookPublisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_book_publisher_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_book_publisher/edit.html.twig', [
            'book_publisher' => $bookPublisher,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_publisher_delete', methods: ['POST'])]
    public function delete(Request $request, BookPublisher $bookPublisher, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$bookPublisher->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($bookPublisher);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_book_publisher_index', [], Response::HTTP_SEE_OTHER);
    }
}
