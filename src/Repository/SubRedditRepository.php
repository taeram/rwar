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
}
