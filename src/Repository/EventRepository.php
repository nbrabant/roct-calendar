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

    /**
     * @param int[]|null $categoryIds
     * @return Event[]
     */
    public function findBySeasonFiltered(
        Season $season,
        ?array $categoryIds = null,
        ?\DateTimeInterface $dateFrom = null,
        ?\DateTimeInterface $dateTo = null,
    ): array {
        $qb = $this->createQueryBuilder('e')
            ->where('e.season = :season')
            ->setParameter('season', $season);

        if ($categoryIds) {
            $qb->join('e.categories', 'c')
                ->andWhere('c.id IN (:categoryIds)')
                ->setParameter('categoryIds', $categoryIds);
        }

        if ($dateFrom) {
            $qb->andWhere('e.eventDate >= :dateFrom')
                ->setParameter('dateFrom', $dateFrom);
        }

        if ($dateTo) {
            $qb->andWhere('e.eventDate <= :dateTo')
                ->setParameter('dateTo', $dateTo);
        }

        return $qb->orderBy('e.eventDate', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
