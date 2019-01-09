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

    public function findByUrl($url) {
        return $this->createQueryBuilder('Wallpaper')
            ->andWhere('Wallpaper.hash = :hash')
            ->setParameter('hash', hash('sha256', $url))
            ->getQuery()
            ->getOneOrNullResult();
    }
}
