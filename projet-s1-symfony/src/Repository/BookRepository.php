<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\Work;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('b.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    /**
     * Find bestseller books based on order line quantities
     * 
     * @param int $limit Maximum number of books to return
     * @return Book[] Returns an array of Book objects
     */
    public function findBestsellers(int $limit = 8): array
    {
        // Get books with their order line counts, ordered by total quantity sold
        $results = $this->createQueryBuilder('b')
            ->leftJoin('b.orderLines', 'ol')
            ->leftJoin('b.work', 'w')
            ->select('b')
            ->addSelect('COALESCE(SUM(ol.quantity), 0) as HIDDEN totalQuantity')
            ->groupBy('b.id')
            ->orderBy('totalQuantity', 'DESC')
            ->addOrderBy('b.id', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // Extract Book entities from results
        return array_map(function ($result) {
            return is_array($result) ? $result[0] : $result;
        }, $results);
    }

    /**
     * Find all books (editions) for a given Work
     * 
     * @param Work $work The work entity
     * @return Book[] Returns an array of Book objects
     */
    public function findByWork(Work $work): array
    {
        return $this->createQueryBuilder('b')
            ->where('b.work = :work')
            ->setParameter('work', $work)
            ->leftJoin('b.bookPublisher', 'bp')
            ->addSelect('bp')
            ->orderBy('b.publicationDate', 'DESC')
            ->addOrderBy('b.currentUnitPrice', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
