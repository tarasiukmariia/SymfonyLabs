<?php

namespace App\Repository;

use App\Entity\Flight;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class FlightRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Flight::class);
    }

    public function getAllFlightsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('f');

        if (!empty($filters['flight_number'])) {
            $qb->andWhere('f.flightNumber LIKE :flightNum')
               ->setParameter('flightNum', '%' . $filters['flight_number'] . '%');
        }

        if (!empty($filters['status'])) {
            $qb->andWhere('f.status = :status')
               ->setParameter('status', $filters['status']);
        }

        if (!empty($filters['departure_airport_id'])) {
            $qb->andWhere('f.departureAirport = :depAirport')
               ->setParameter('depAirport', $filters['departure_airport_id']);
        }

        if (!empty($filters['arrival_airport_id'])) {
            $qb->andWhere('f.arrivalAirport = :arrAirport')
               ->setParameter('arrAirport', $filters['arrival_airport_id']);
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