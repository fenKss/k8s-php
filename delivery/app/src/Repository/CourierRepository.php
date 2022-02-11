<?php

namespace App\Repository;

use App\Entity\Courier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Courier|null find($id, $lockMode = null, $lockVersion = null)
 * @method Courier|null findOneBy(array $criteria, array $orderBy = null)
 * @method Courier[]    findAll()
 * @method Courier[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Courier::class);
    }

    public function getFirstFree(): ?Courier
    {
        return $this->createQueryBuilder('c')
                    ->where('c.isReserved = 0')
                    ->setMaxResults(1)
                    ->getQuery()
                    ->getOneOrNullResult();
    }
    // /**
    //  * @return Courier[] Returns an array of Courier objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Courier
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
