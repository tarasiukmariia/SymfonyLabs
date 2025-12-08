<?php

namespace App\Service;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

class CountryService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createCountry(array $data): Country
    {
        $country = new Country();
        $country->setName($data['name']);
        $country->setCode($data['code']);

        $this->entityManager->persist($country);
        $this->entityManager->flush();

        return $country;
    }
}