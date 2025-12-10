<?php

namespace App\Repository;

use App\Entity\Airport;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AirportRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Airport::class);
    }

    public function getAllAirportsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($filters['name'])) {
            $qb->andWhere('a.name LIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['iata_code'])) {
            $qb->andWhere('a.iataCode LIKE :iataCode')
               ->setParameter('iataCode', '%' . $filters['iata_code'] . '%');
        }

        if (!empty($filters['city'])) {
            $qb->andWhere('a.city LIKE :city')
               ->setParameter('city', '%' . $filters['city'] . '%');
        }

        if (!empty($filters['country_id'])) {
            $qb->andWhere('a.country = :countryId')
               ->setParameter('countryId', $filters['country_id']);
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