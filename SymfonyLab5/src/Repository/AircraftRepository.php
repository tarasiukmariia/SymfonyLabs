<?php

namespace App\Repository;

use App\Entity\Aircraft;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AircraftRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Aircraft::class);
    }

    public function getAllAircraftsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('a');

        if (!empty($filters['registrationNumber'])) {
            $qb->andWhere('a.registrationNumber LIKE :regNum')
               ->setParameter('regNum', '%' . $filters['registrationNumber'] . '%');
        }

        if (!empty($filters['minCapacity'])) {
            $qb->andWhere('a.totalCapacity >= :minCap')
               ->setParameter('minCap', $filters['minCapacity']);
        }

        if (!empty($filters['model_id'])) {
            $qb->andWhere('a.model = :modelId')
               ->setParameter('modelId', $filters['model_id']);
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