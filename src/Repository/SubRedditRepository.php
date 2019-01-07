<?php

namespace App\Repository;

use App\Entity\SubReddit;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method SubReddit|null find($id, $lockMode = null, $lockVersion = null)
 * @method SubReddit|null findOneBy(array $criteria, array $orderBy = null)
 * @method SubReddit[]    findAll()
 * @method SubReddit[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SubRedditRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, SubReddit::class);
    }

    // /**
    //  * @return SubReddit[] Returns an array of SubReddit objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SubReddit
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
