<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\BookPublisher;
use App\Entity\Customer;
use App\Entity\Genre;
use App\Entity\MonthlyBox;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\Subscription;
use App\Entity\SubscriptionOffer;
use App\Entity\Work;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // ========== ADDRESSES ==========
        $address1 = new Address();
        $address1->setLabel('Domicile');
        $address1->setRecipientName('Admin Utilisateur');
        $address1->setStreet('1 Rue de l\'Admin');
        $address1->setAddressDetails('Bâtiment A, 3ème étage');
        $address1->setPostalCode('75001');
        $address1->setCity('Paris');
        $address1->setCountry('France');
        $manager->persist($address1);

        $address2 = new Address();
        $address2->setLabel('Maison');
        $address2->setRecipientName('John Doe');
        $address2->setStreet('2 Avenue des Utilisateurs');
        $address2->setAddressDetails('Appartement 12');
        $address2->setPostalCode('69001');
        $address2->setCity('Lyon');
        $address2->setCountry('France');
        $manager->persist($address2);

        $address3 = new Address();
        $address3->setLabel('Bureau');
        $address3->setRecipientName('Jean Martin');
        $address3->setStreet('15 Boulevard de la Lecture');
        $address3->setAddressDetails('');
        $address3->setPostalCode('33000');
        $address3->setCity('Bordeaux');
        $address3->setCountry('France');
        $manager->persist($address3);

        // ========== CUSTOMERS ==========
        $admin = new Customer();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $admin->setFirstName('Admin');
        $admin->setLastName('Utilisateur');
        $admin->setPhone('+33612345678');
        $admin->setRegistrationDate(new \DateTime('2024-01-15'));
        $admin->setLastLogin(new \DateTime('2026-02-06'));
        $admin->setAddress($address1);
        $manager->persist($admin);

        $user = new Customer();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $user->setFirstName('John');
        $user->setLastName('Doe');
        $user->setPhone('+33698765432');
        $user->setRegistrationDate(new \DateTime('2024-06-10'));
        $user->setLastLogin(new \DateTime('2026-02-05'));
        $user->setAddress($address2);
        $manager->persist($user);

        $customer3 = new Customer();
        $customer3->setEmail('jean.martin@example.com');
        $customer3->setRoles(['ROLE_USER']);
        $customer3->setPassword($this->passwordHasher->hashPassword($customer3, 'password123'));
        $customer3->setFirstName('Jean');
        $customer3->setLastName('Martin');
        $customer3->setPhone('+33687654321');
        $customer3->setRegistrationDate(new \DateTime('2025-03-20'));
        $customer3->setLastLogin(new \DateTime('2026-02-04'));
        $customer3->setAddress($address3);
        $manager->persist($customer3);

        // ========== GENRES ==========
        $genreRomance = new Genre();
        $genreRomance->setLabel('Romance');
        $manager->persist($genreRomance);

        $genreSF = new Genre();
        $genreSF->setLabel('Science-Fiction');
        $manager->persist($genreSF);

        $genrePolicier = new Genre();
        $genrePolicier->setLabel('Policier');
        $manager->persist($genrePolicier);

        $genreFantasy = new Genre();
        $genreFantasy->setLabel('Fantasy');
        $manager->persist($genreFantasy);

        $genreThriller = new Genre();
        $genreThriller->setLabel('Thriller');
        $manager->persist($genreThriller);

        // ========== AUTHORS ==========
        $author1 = new Author();
        $author1->setFirstName('Victor');
        $author1->setLastName('Hugo');
        $author1->setBiography('Écrivain français majeur du XIXe siècle, connu pour Les Misérables et Notre-Dame de Paris.');
        $author1->setPhotoUrl('https://example.com/photos/victor-hugo.jpg');
        $manager->persist($author1);

        $author2 = new Author();
        $author2->setFirstName('J.K.');
        $author2->setLastName('Rowling');
        $author2->setBiography('Auteure britannique, créatrice de la saga Harry Potter.');
        $author2->setPhotoUrl('https://example.com/photos/jk-rowling.jpg');
        $manager->persist($author2);

        $author3 = new Author();
        $author3->setFirstName('Agatha');
        $author3->setLastName('Christie');
        $author3->setBiography('Romancière britannique, reine du crime, créatrice de Hercule Poirot.');
        $author3->setPhotoUrl('https://example.com/photos/agatha-christie.jpg');
        $manager->persist($author3);

        $author4 = new Author();
        $author4->setFirstName('Isaac');
        $author4->setLastName('Asimov');
        $author4->setBiography('Écrivain américano-russe, maître de la science-fiction.');
        $author4->setPhotoUrl('https://example.com/photos/isaac-asimov.jpg');
        $manager->persist($author4);

        // ========== PUBLISHERS ==========
        $publisher1 = new BookPublisher();
        $publisher1->setPublisherName('Gallimard');
        $publisher1->setContactEmail('contact@gallimard.fr');
        $publisher1->setWebsite('https://www.gallimard.fr');
        $manager->persist($publisher1);

        $publisher2 = new BookPublisher();
        $publisher2->setPublisherName('Hachette');
        $publisher2->setContactEmail('info@hachette.fr');
        $publisher2->setWebsite('https://www.hachette.fr');
        $manager->persist($publisher2);

        $publisher3 = new BookPublisher();
        $publisher3->setPublisherName('Le Livre de Poche');
        $publisher3->setContactEmail('contact@livredepoche.com');
        $publisher3->setWebsite('https://www.livredepoche.com');
        $manager->persist($publisher3);

        // ========== WORKS ==========
        $work1 = new Work();
        $work1->setId('OL_FIXTURE_1W');
        $work1->setTitle('Les Misérables');
        $work1->setSummary('Un ancien forçat, Jean Valjean, cherche la rédemption dans la France du XIXe siècle.');
        $work1->addGenre($genreRomance);
        $work1->addAuthor($author1);
        $manager->persist($work1);

        $work2 = new Work();
        $work2->setId('OL_FIXTURE_2W');
        $work2->setTitle('Harry Potter à l\'école des sorciers');
        $work2->setSummary('Un jeune sorcier découvre son héritage magique et entre à Poudlard.');
        $work2->addGenre($genreFantasy);
        $work2->addAuthor($author2);
        $manager->persist($work2);

        $work3 = new Work();
        $work3->setId('OL_FIXTURE_3W');
        $work3->setTitle('Le Crime de l\'Orient-Express');
        $work3->setSummary('Hercule Poirot enquête sur un meurtre dans un train de luxe.');
        $work3->addGenre($genrePolicier);
        $work3->addGenre($genreThriller);
        $work3->addAuthor($author3);
        $manager->persist($work3);

        $work4 = new Work();
        $work4->setId('OL_FIXTURE_4W');
        $work4->setTitle('Fondation');
        $work4->setSummary('La saga épique de la chute et de la renaissance d\'un empire galactique.');
        $work4->addGenre($genreSF);
        $work4->addAuthor($author4);
        $manager->persist($work4);

        $work5 = new Work();
        $work5->setId('OL_FIXTURE_5W');
        $work5->setTitle('Notre-Dame de Paris');
        $work5->setSummary('L\'histoire tragique de Quasimodo et Esmeralda dans le Paris médiéval.');
        $work5->addGenre($genreRomance);
        $work5->addAuthor($author1);
        $manager->persist($work5);

        // ========== BOOKS ==========
        $book1 = new Book();
        $book1->setId('OL_FIXTURE_1M');
        $book1->setWork($work1);
        $book1->setBookPublisher($publisher1);
        $book1->setPublicationDate(new \DateTime('1862-04-03'));
        $book1->setReleaseDate(new \DateTime('2020-01-15'));
        $book1->setCurrentUnitPrice(1590);
        $book1->setAvailableStock(50);
        $book1->setCoverImageUrl('https://example.com/covers/les-miserables.jpg');
        $book1->setWeightGrams(850);
        $manager->persist($book1);

        $book2 = new Book();
        $book2->setId('OL_FIXTURE_2M');
        $book2->setWork($work2);
        $book2->setBookPublisher($publisher2);
        $book2->setPublicationDate(new \DateTime('1997-06-26'));
        $book2->setReleaseDate(new \DateTime('2021-09-01'));
        $book2->setCurrentUnitPrice(1290);
        $book2->setAvailableStock(120);
        $book2->setCoverImageUrl('https://example.com/covers/harry-potter-1.jpg');
        $book2->setWeightGrams(320);
        $manager->persist($book2);

        $book3 = new Book();
        $book3->setId('OL_FIXTURE_3M');
        $book3->setWork($work3);
        $book3->setBookPublisher($publisher3);
        $book3->setPublicationDate(new \DateTime('1934-01-01'));
        $book3->setReleaseDate(new \DateTime('2019-05-10'));
        $book3->setCurrentUnitPrice(890);
        $book3->setAvailableStock(75);
        $book3->setCoverImageUrl('https://example.com/covers/orient-express.jpg');
        $book3->setWeightGrams(280);
        $manager->persist($book3);

        $book4 = new Book();
        $book4->setId('OL_FIXTURE_4M');
        $book4->setWork($work4);
        $book4->setBookPublisher($publisher1);
        $book4->setPublicationDate(new \DateTime('1951-05-01'));
        $book4->setReleaseDate(new \DateTime('2022-03-15'));
        $book4->setCurrentUnitPrice(1190);
        $book4->setAvailableStock(60);
        $book4->setCoverImageUrl('https://example.com/covers/fondation.jpg');
        $book4->setWeightGrams(410);
        $manager->persist($book4);

        $book5 = new Book();
        $book5->setId('OL_FIXTURE_5M');
        $book5->setWork($work5);
        $book5->setBookPublisher($publisher2);
        $book5->setPublicationDate(new \DateTime('1831-03-16'));
        $book5->setReleaseDate(new \DateTime('2020-11-20'));
        $book5->setCurrentUnitPrice(1390);
        $book5->setAvailableStock(45);
        $book5->setCoverImageUrl('https://example.com/covers/notre-dame.jpg');
        $book5->setWeightGrams(550);
        $manager->persist($book5);

        // ========== SUBSCRIPTION OFFERS ==========
        $offer1 = new SubscriptionOffer();
        $offer1->setOfferName('Formule Découverte');
        $offer1->setDescription('Parfait pour les nouveaux lecteurs : 1 livre par mois soigneusement sélectionné.');
        $offer1->setMonthlyPrice(1990);
        $offer1->setIncludedBooksCount(1);
        $offer1->setCommitmentMonths(3);
        $manager->persist($offer1);

        $offer2 = new SubscriptionOffer();
        $offer2->setOfferName('Formule Passionné');
        $offer2->setDescription('Pour les lecteurs assidus : 2 livres par mois pour ne jamais manquer de lecture.');
        $offer2->setMonthlyPrice(3490);
        $offer2->setIncludedBooksCount(2);
        $offer2->setCommitmentMonths(6);
        $manager->persist($offer2);

        $offer3 = new SubscriptionOffer();
        $offer3->setOfferName('Formule Dévoreur');
        $offer3->setDescription('Pour les grands lecteurs : 3 livres par mois, une bibliothèque en constante évolution.');
        $offer3->setMonthlyPrice(4990);
        $offer3->setIncludedBooksCount(3);
        $offer3->setCommitmentMonths(12);
        $manager->persist($offer3);

        // ========== SUBSCRIPTIONS ==========
        $subscription1 = new Subscription();
        $subscription1->setCustomer($user);
        $subscription1->setSubscriptionOffer($offer2);
        $subscription1->setAddress($address2);
        $subscription1->setStartDate(new \DateTime('2025-01-01'));
        $subscription1->setExpectedEndDate(new \DateTime('2025-07-01'));
        $subscription1->setStatus('active');
        $subscription1->setNextPaymentDate(new \DateTime('2026-03-01'));
        $manager->persist($subscription1);

        $subscription2 = new Subscription();
        $subscription2->setCustomer($customer3);
        $subscription2->setSubscriptionOffer($offer1);
        $subscription2->setAddress($address3);
        $subscription2->setStartDate(new \DateTime('2025-06-01'));
        $subscription2->setExpectedEndDate(new \DateTime('2025-09-01'));
        $subscription2->setStatus('active');
        $subscription2->setNextPaymentDate(new \DateTime('2026-03-01'));
        $manager->persist($subscription2);

        // ========== MONTHLY BOXES ==========
        $monthlyBox1 = new MonthlyBox();
        $monthlyBox1->setSubscription($subscription1);
        $monthlyBox1->setReferenceMonth('2026-01');
        $monthlyBox1->setCreationDate(new \DateTime('2026-01-05'));
        $monthlyBox1->setShippingStatus('delivered');
        $manager->persist($monthlyBox1);

        $monthlyBox2 = new MonthlyBox();
        $monthlyBox2->setSubscription($subscription1);
        $monthlyBox2->setReferenceMonth('2026-02');
        $monthlyBox2->setCreationDate(new \DateTime('2026-02-05'));
        $monthlyBox2->setShippingStatus('in_transit');
        $manager->persist($monthlyBox2);

        $monthlyBox3 = new MonthlyBox();
        $monthlyBox3->setSubscription($subscription2);
        $monthlyBox3->setReferenceMonth('2026-02');
        $monthlyBox3->setCreationDate(new \DateTime('2026-02-03'));
        $monthlyBox3->setShippingStatus('preparing');
        $manager->persist($monthlyBox3);

        // ========== ORDERS ==========
        $order1 = new Order();
        $order1->setCustomer($user);
        $order1->setAddress($address2);
        $order1->setMonthlyBox($monthlyBox1);
        $order1->setOrderDate(new \DateTime('2026-01-10'));
        $order1->setStatus('delivered');
        $order1->setTotalAmount(3990);
        $order1->setShippingCost(500);
        $order1->setTrackingNumber('FR123456789');
        $manager->persist($order1);

        $order2 = new Order();
        $order2->setCustomer($admin);
        $order2->setAddress($address1);
        $order2->setOrderDate(new \DateTime('2026-01-20'));
        $order2->setStatus('shipped');
        $order2->setTotalAmount(2680);
        $order2->setShippingCost(500);
        $order2->setTrackingNumber('FR987654321');
        $manager->persist($order2);

        $order3 = new Order();
        $order3->setCustomer($customer3);
        $order3->setAddress($address3);
        $order3->setMonthlyBox($monthlyBox3);
        $order3->setOrderDate(new \DateTime('2026-02-05'));
        $order3->setStatus('processing');
        $order3->setTotalAmount(2390);
        $order3->setShippingCost(500);
        $order3->setTrackingNumber('FR456789123');
        $manager->persist($order3);

        // ========== ORDER LINES ==========
        $orderLine1 = new OrderLine();
        $orderLine1->setTableOrder($order1);
        $orderLine1->setBook($book2);
        $orderLine1->setQuantity(2);
        $orderLine1->setUnitPriceSnapshot(1290);
        $manager->persist($orderLine1);

        $orderLine2 = new OrderLine();
        $orderLine2->setTableOrder($order1);
        $orderLine2->setBook($book3);
        $orderLine2->setQuantity(1);
        $orderLine2->setUnitPriceSnapshot(890);
        $manager->persist($orderLine2);

        $orderLine3 = new OrderLine();
        $orderLine3->setTableOrder($order2);
        $orderLine3->setBook($book1);
        $orderLine3->setQuantity(1);
        $orderLine3->setUnitPriceSnapshot(1590);
        $manager->persist($orderLine3);

        $orderLine4 = new OrderLine();
        $orderLine4->setTableOrder($order2);
        $orderLine4->setBook($book5);
        $orderLine4->setQuantity(1);
        $orderLine4->setUnitPriceSnapshot(1390);
        $manager->persist($orderLine4);

        $orderLine5 = new OrderLine();
        $orderLine5->setTableOrder($order3);
        $orderLine5->setBook($book4);
        $orderLine5->setQuantity(1);
        $orderLine5->setUnitPriceSnapshot(1190);
        $manager->persist($orderLine5);

        $orderLine6 = new OrderLine();
        $orderLine6->setTableOrder($order3);
        $orderLine6->setBook($book3);
        $orderLine6->setQuantity(1);
        $orderLine6->setUnitPriceSnapshot(890);
        $manager->persist($orderLine6);

        $manager->flush();
    }
}
