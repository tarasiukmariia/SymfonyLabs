<?php

namespace App\Repository;

use App\Entity\Booking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class BookingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Booking::class);
    }

    public function getAllBookingsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('b');

        if (!empty($filters['booking_reference'])) {
            $qb->andWhere('b.bookingReference LIKE :ref')
               ->setParameter('ref', '%' . $filters['booking_reference'] . '%');
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('b.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['booker_id'])) {
            $qb->andWhere('b.booker = :bookerId')
               ->setParameter('bookerId', $filters['booker_id']);
        }

        if (!empty($filters['min_amount'])) {
            $qb->andWhere('b.totalAmount >= :minAmount')
               ->setParameter('minAmount', $filters['min_amount']);
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