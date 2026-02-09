<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Season;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    /**
     * @return Event[]
     */
    public function findBySeason(Season $season): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.season = :season')
            ->setParameter('season', $season)
            ->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
