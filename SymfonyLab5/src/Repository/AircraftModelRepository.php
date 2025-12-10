<?php

namespace App\Repository;

use App\Entity\AircraftModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class AircraftModelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AircraftModel::class);
    }

    public function getAllAircraftModelsByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('m');

        if (!empty($filters['manufacturer'])) {
            $qb->andWhere('m.manufacturer LIKE :manufacturer')
               ->setParameter('manufacturer', '%' . $filters['manufacturer'] . '%');
        }

        if (!empty($filters['model_name'])) {
            $qb->andWhere('m.modelName LIKE :modelName')
               ->setParameter('modelName', '%' . $filters['model_name'] . '%');
        }

        if (!empty($filters['min_range'])) {
            $qb->andWhere('m.maxRangeKm >= :minRange')
               ->setParameter('minRange', $filters['min_range']);
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