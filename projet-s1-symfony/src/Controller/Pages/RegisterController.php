<?php

namespace App\Controller\Pages;

use App\Entity\Customer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;

final class RegisterController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        UserAuthenticatorInterface $userAuthenticator,
        #[Autowire(service: 'security.authenticator.form_login.main')] AuthenticatorInterface $authenticator
    ): Response {
        $customer = new Customer();
        $error = null;

        if ($request->isMethod('POST')) {
            $customer->setLastName($request->request->get('lastName', ''));
            $customer->setFirstName($request->request->get('firstName', ''));
            $customer->setEmail($request->request->get('email', ''));
            $customer->setPhone($request->request->get('phone', ''));
            $plainPassword = $request->request->get('password', '');
            if ($plainPassword) {
                $hashedPassword = $passwordHasher->hashPassword($customer, $plainPassword);
                $customer->setPassword($hashedPassword);
            }

            if (!$customer->getEmail() || !$plainPassword || !$customer->getLastName() || !$customer->getFirstName()) {
                $error = 'Veuillez remplir tous les champs obligatoires.';
            } else {
                $customer->setRegistrationDate(new \DateTime());
                $customer->setRoles(['ROLE_USER']);
                $em->persist($customer);
                $em->flush();

                return $userAuthenticator->authenticateUser(
                    $customer,
                    $authenticator,
                    $request
                );
            }
        } else {
            // Initialisation des propriétés pour éviter l'erreur Twig avec les propriétés typées
            $customer->setLastName('');
            $customer->setFirstName('');
            $customer->setEmail('');
            $customer->setPhone('');
        }

        return $this->render('register/index.html.twig', [
            'customer' => $customer,
            'error' => $error,
        ]);
    }

    #[Route('/register/success', name: 'app_register_success')]
    public function success(): Response
    {
        return new Response('Inscription réussie !');
    }
}
