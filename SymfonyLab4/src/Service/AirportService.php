<?php

namespace App\Service;

use App\Entity\Airport;
use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AirportService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createAirport(
        string $name,
        string $iataCode,
        int $countryId,
        string $city
    ): Airport {
        $country = $this->entityManager->getRepository(Country::class)->find($countryId);
        
        if (!$country) {
            throw new NotFoundHttpException('Country not found with id ' . $countryId);
        }

        $airport = $this->createAirportObject($name, $iataCode, $country, $city);

        $this->requestCheckerService->validateRequestDataByConstraints($airport);

        $this->entityManager->persist($airport);
        $this->entityManager->flush();

        return $airport;
    }

    private function createAirportObject(
        string $name,
        string $iataCode,
        Country $country,
        string $city
    ): Airport {
        $airport = new Airport();
        $airport->setName($name);
        $airport->setIataCode($iataCode);
        $airport->setCountry($country);
        $airport->setCity($city);

        return $airport;
    }

    public function updateAirport(Airport $airport, array $data): void
    {
        if (array_key_exists('country_id', $data)) {
            $country = $this->entityManager->getRepository(Country::class)->find($data['country_id']);
            if (!$country) {
                throw new NotFoundHttpException('Country not found');
            }
            $airport->setCountry($country);
            unset($data['country_id']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($airport, $method)) {
                $airport->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($airport);
        
        $this->entityManager->flush();
    }
}