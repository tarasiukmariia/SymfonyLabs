<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function getAllTicketsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('t');

        if (!empty($filters['booking_id'])) {
            $qb->andWhere('t.booking = :bookingId')
               ->setParameter('bookingId', $filters['booking_id']);
        }

        if (!empty($filters['flight_id'])) {
            $qb->andWhere('t.flight = :flightId')
               ->setParameter('flightId', $filters['flight_id']);
        }

        if (!empty($filters['passenger_id'])) {
            $qb->andWhere('t.passenger = :passengerId')
               ->setParameter('passengerId', $filters['passenger_id']);
        }

        if (!empty($filters['travel_class_id'])) {
            $qb->andWhere('t.travelClass = :classId')
               ->setParameter('classId', $filters['travel_class_id']);
        }

        if (!empty($filters['min_price'])) {
            $qb->andWhere('t.price >= :minPrice')
               ->setParameter('minPrice', $filters['min_price']);
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