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

    protected function getUnratedQueryBuilder()
    {
        return $this->createQueryBuilder('SubReddit')
            ->leftJoin('SubReddit.wallpapers', 'Wallpaper')
            ->andWhere('Wallpaper.rating = 0');
    }

    /**
     * Find the number of unrated wallpapers for the selected subreddit.
     *
     * @param integer $id The subreddit id.
     *
     * @return int|null
     *
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findCountUnrated($id): ?int
    {
        return $this->getUnratedQueryBuilder()
            ->select('COUNT(Wallpaper.id)')
            ->andWhere('SubReddit.id = :subredditId')
            ->setParameter('subredditId', $id)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Find a random subreddit which has unrated wallpapers.
     *
     * @return \App\Entity\SubReddit|null
     *
     * @throws \Exception
     */
    public function findRandomUnrated()
    {
        $subreddits = $this->getUnratedQueryBuilder()
            ->select('DISTINCT SubReddit')
            ->getQuery()
            ->getResult();

        if (count($subreddits) === 0) {
            return null;
        }

        return $subreddits[random_int(0, count($subreddits) - 1)];
    }
}
