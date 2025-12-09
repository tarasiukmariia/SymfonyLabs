<?php

namespace App\Repository;

use App\Entity\Baggage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BaggageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Baggage::class);
    }

    public function getAllBaggageByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('b');

        if (!empty($filters['ticket_id'])) {
            $qb->andWhere('b.ticket = :ticketId')
               ->setParameter('ticketId', $filters['ticket_id']);
        }

        if (!empty($filters['type'])) {
            $qb->andWhere('b.type = :type')
               ->setParameter('type', $filters['type']);
        }

        if (!empty($filters['min_weight'])) {
            $qb->andWhere('b.weightKg >= :minWeight')
               ->setParameter('minWeight', $filters['min_weight']);
        }

        $paginator = new Paginator($qb);
        
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $itemsPerPage);

        $paginator
            ->getQuery()
            ->setFirstResult($itemsPerPage * ($page - 1))
            ->setMaxResults($itemsPerPage);

        return [
            'data' => $paginator->getQuery()->getResult(),
            'meta' => [
                'totalItems' => $totalItems,
                'page' => $page,
                'itemsPerPage' => $itemsPerPage,
                'totalPages' => $pagesCount
            ]
        ];
    }
}