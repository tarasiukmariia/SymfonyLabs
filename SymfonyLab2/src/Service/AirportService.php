<?php

namespace App\Service;

use App\Entity\Airport;
use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AirportService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createAirport(array $data): Airport
    {
        $country = $this->entityManager->getRepository(Country::class)->find($data['country_id']);
        
        if (!$country) {
            throw new NotFoundHttpException('Country not found');
        }

        $airport = new Airport();
        $airport->setName($data['name']);
        $airport->setIataCode($data['iata_code']);
        $airport->setCity($data['city'] ?? ''); 
        $airport->setCountry($country);

        $this->entityManager->persist($airport);
        $this->entityManager->flush();

        return $airport;
    }
}