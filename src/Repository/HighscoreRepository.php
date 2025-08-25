<?php

namespace App\Repository;

use App\Entity\Highscore;
use App\Entity\Race;
use App\Entity\ScavangerHunt;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Highscore>
 */
class HighscoreRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Highscore::class);
    }

    public function getScavengerHuntHighScores(ScavangerHunt $scavangerHunt): mixed
    {
      $a = $scavangerHunt->getId();
      $b = 1;
      return $this->createQueryBuilder('h')
        ->andWhere('h.scavenger_hunt = :scavenger_hunt')
        ->setParameter('scavenger_hunt', $scavangerHunt->getId())
        ->orderBy('h.progress_task_solution', 'DESC')
        ->addOrderBy('h.progress_task_entry', 'DESC')
        ->addOrderBy('h.time', 'ASC')
        ->getQuery()->getResult();
    }

    public function getRaceHighScores(Race $race): mixed
    {
      return $this->createQueryBuilder('h')
        ->andWhere('h.race = :race')
        ->setParameter('race', $race->getId())
        ->orderBy('h.progress_task_solution', 'DESC')
        ->addOrderBy('h.progress_task_entry', 'DESC')
        ->addOrderBy('h.time', 'ASC')
        ->getQuery()->getResult();
    }
}
