<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\BookRepository;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/book')]
final class AdminBookController extends AbstractController
{
    #[Route(name: 'app_admin_book_index', methods: ['GET'])]
    public function index(BookRepository $bookRepository): Response
    {
        return $this->render('admin_book/index.html.twig', [
            'books' => $bookRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_book_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($book);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_book/new.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_show', methods: ['GET'])]
    public function show(Request $request, Book $book): Response
    {
        $returnOrderId = $request->query->getInt('return_order');

        return $this->render('admin_book/show.html.twig', [
            'book' => $book,
            'return_order' => $returnOrderId ?: null,
        ]);
    }

    #[Route('/{id}/restock', name: 'app_admin_book_restock', methods: ['POST'])]
    public function restock(Request $request, Book $book, BookService $bookService): Response
    {
        $returnOrderId = $request->request->getInt('return_order');

        $params = ['id' => $book->getId()];
        if ($returnOrderId > 0) {
            $params['return_order'] = $returnOrderId;
        }
        $newQuantity = (int) $request->request->get('new_quantity', 0);
        if ($newQuantity < 0) {
            $this->addFlash('error', 'La quantité doit être positive.');
            return $this->redirectToRoute('app_admin_book_show', $params, Response::HTTP_SEE_OTHER);
        }
        if (!$this->isCsrfTokenValid('restock'.$book->getId(), $request->request->getString('_token'))) {
            $this->addFlash('error', 'Jeton de sécurité invalide.');
            return $this->redirectToRoute('app_admin_book_show', $params, Response::HTTP_SEE_OTHER);
        }

        $bookService->restock($book, $newQuantity);

        $title = $book->getWork()?->getTitle() ?? $book->getId();
        $this->addFlash('success', sprintf('Stock mis à jour pour « %s ».', $title));

        if ($returnOrderId) {
            return $this->redirectToRoute('app_admin_order_show', ['id' => $returnOrderId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_admin_book_show', ['id' => $book->getId()], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/edit', name: 'app_admin_book_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Book $book, EntityManagerInterface $entityManager, BookService $bookService): Response
    {
        $form = $this->createForm(BookType::class, $book, ['is_edit' => true]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $updatedCount = $bookService->syncOrdersWithBookStock($book);
            $availableStock = $book->getAvailableStock() ?? 0;

            if ($updatedCount > 0) {
                if ($availableStock === 0) {
                    $this->addFlash('success', $updatedCount === 1
                        ? 'Livre mis à jour. 1 commande repassée en attente de réappro.'
                        : sprintf('Livre mis à jour. %d commandes repassées en attente de réappro.', $updatedCount));
                } else {
                    $this->addFlash('success', $updatedCount === 1
                        ? 'Livre mis à jour. 1 commande prête à être expédiée.'
                        : sprintf('Livre mis à jour. %d commandes prêtes à être expédiées.', $updatedCount));
                }
            } else {
                $this->addFlash('success', 'Livre mis à jour.');
            }

            return $this->redirectToRoute('app_admin_book_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_book/edit.html.twig', [
            'book' => $book,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_book_delete', methods: ['POST'])]
    public function delete(Request $request, Book $book, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$book->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($book);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_book_index', [], Response::HTTP_SEE_OTHER);
    }
}
