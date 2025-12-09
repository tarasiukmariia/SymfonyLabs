<?php

namespace App\Repository;

use App\Entity\Passenger;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class PassengerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Passenger::class);
    }

    public function getAllPassengersByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('p');

        if (!empty($filters['first_name'])) {
            $qb->andWhere('p.firstName LIKE :firstName')
               ->setParameter('firstName', '%' . $filters['first_name'] . '%');
        }

        if (!empty($filters['last_name'])) {
            $qb->andWhere('p.lastName LIKE :lastName')
               ->setParameter('lastName', '%' . $filters['last_name'] . '%');
        }

        if (!empty($filters['email'])) {
            $qb->andWhere('p.email LIKE :email')
               ->setParameter('email', '%' . $filters['email'] . '%');
        }

        if (!empty($filters['passport_number'])) {
            $qb->andWhere('p.passportNumber LIKE :passport')
               ->setParameter('passport', '%' . $filters['passport_number'] . '%');
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