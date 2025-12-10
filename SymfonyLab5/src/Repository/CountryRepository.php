<?php

namespace App\Repository;

use App\Entity\Country;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

class CountryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Country::class);
    }

    public function getAllCountriesByFilter(array $filters, int $itemsPerPage, int $page): array
    {
        $qb = $this->createQueryBuilder('c');

        // Фільтр по назві
        if (!empty($filters['name'])) {
            $qb->andWhere('c.name LIKE :name')
               ->setParameter('name', '%' . $filters['name'] . '%');
        }

        // Фільтр по коду
        if (!empty($filters['code'])) {
            $qb->andWhere('c.code LIKE :code')
               ->setParameter('code', '%' . $filters['code'] . '%');
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