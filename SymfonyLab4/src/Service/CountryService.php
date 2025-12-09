<?php

namespace App\Service;

use App\Entity\Country;
use Doctrine\ORM\EntityManagerInterface;

class CountryService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createCountry(string $name, string $code): Country
    {
        $country = $this->createCountryObject($name, $code);

        $this->requestCheckerService->validateRequestDataByConstraints($country);

        $this->entityManager->persist($country);
        $this->entityManager->flush();

        return $country;
    }

    private function createCountryObject(string $name, string $code): Country
    {
        $country = new Country();
        $country->setName($name);
        $country->setCode($code);

        return $country;
    }

    public function updateCountry(Country $country, array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($country, $method)) {
                $country->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($country);
        
        $this->entityManager->flush();
    }
}