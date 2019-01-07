<?php

namespace App\Repository\SubReddit;

use App\Entity\SubReddit\Wallpaper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Wallpaper|null find($id, $lockMode = null, $lockVersion = null)
 * @method Wallpaper|null findOneBy(array $criteria, array $orderBy = null)
 * @method Wallpaper[]    findAll()
 * @method Wallpaper[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class WallpaperRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Wallpaper::class);
    }

    // /**
    //  * @return Wallpaper[] Returns an array of Wallpaper objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('w.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Wallpaper
    {
        return $this->createQueryBuilder('w')
            ->andWhere('w.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
