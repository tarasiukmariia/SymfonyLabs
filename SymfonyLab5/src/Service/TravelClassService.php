<?php

namespace App\Service;

use App\Entity\TravelClass;
use Doctrine\ORM\EntityManagerInterface;

class TravelClassService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createTravelClass(string $name, string $priceMultiplier): TravelClass
    {
        $class = $this->createTravelClassObject($name, $priceMultiplier);

        $this->requestCheckerService->validateRequestDataByConstraints($class);

        $this->entityManager->persist($class);
        $this->entityManager->flush();

        return $class;
    }

    private function createTravelClassObject(string $name, string $priceMultiplier): TravelClass
    {
        $class = new TravelClass();
        $class->setName($name);
        $class->setPriceMultiplier($priceMultiplier);

        return $class;
    }

    public function updateTravelClass(TravelClass $class, array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($class, $method)) {
                $class->$method((string)$value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($class);
        
        $this->entityManager->flush();
    }
}