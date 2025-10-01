<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Game>
 */
class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('g')
            ->orderBy('g.playedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findInProgressGames(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'in_progress')
            ->orderBy('g.playedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findCompletedGames(): array
    {
        return $this->createQueryBuilder('g')
            ->where('g.status = :status')
            ->setParameter('status', 'completed')
            ->orderBy('g.playedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
