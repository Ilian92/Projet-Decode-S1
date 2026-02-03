<?php

namespace App\Controller;

use App\Entity\SubscriptionOffer;
use App\Form\SubscriptionOfferType;
use App\Repository\SubscriptionOfferRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/subscription-offer')]
final class AdminSubscriptionOfferController extends AbstractController
{
    #[Route(name: 'app_admin_subscription_offer_index', methods: ['GET'])]
    public function index(SubscriptionOfferRepository $subscriptionOfferRepository): Response
    {
        return $this->render('admin_subscription_offer/index.html.twig', [
            'subscription_offers' => $subscriptionOfferRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_admin_subscription_offer_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $subscriptionOffer = new SubscriptionOffer();
        $form = $this->createForm(SubscriptionOfferType::class, $subscriptionOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($subscriptionOffer);
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_subscription_offer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_subscription_offer/new.html.twig', [
            'subscription_offer' => $subscriptionOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_subscription_offer_show', methods: ['GET'])]
    public function show(SubscriptionOffer $subscriptionOffer): Response
    {
        return $this->render('admin_subscription_offer/show.html.twig', [
            'subscription_offer' => $subscriptionOffer,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_subscription_offer_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubscriptionOffer $subscriptionOffer, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SubscriptionOfferType::class, $subscriptionOffer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_admin_subscription_offer_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin_subscription_offer/edit.html.twig', [
            'subscription_offer' => $subscriptionOffer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_subscription_offer_delete', methods: ['POST'])]
    public function delete(Request $request, SubscriptionOffer $subscriptionOffer, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subscriptionOffer->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($subscriptionOffer);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_admin_subscription_offer_index', [], Response::HTTP_SEE_OTHER);
    }
}
