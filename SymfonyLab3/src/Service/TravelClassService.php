<?php

namespace App\Service;

use App\Entity\TravelClass;
use Doctrine\ORM\EntityManagerInterface;

class TravelClassService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createTravelClass(array $data): TravelClass
    {
        $class = new TravelClass();
        $class->setName($data['name']);
        $class->setPriceMultiplier((string)$data['price_multiplier']);

        $this->entityManager->persist($class);
        $this->entityManager->flush();

        return $class;
    }
}