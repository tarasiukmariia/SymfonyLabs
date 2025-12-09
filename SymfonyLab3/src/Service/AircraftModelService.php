<?php

namespace App\Service;

use App\Entity\AircraftModel;
use Doctrine\ORM\EntityManagerInterface;

class AircraftModelService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createAircraftModel(
        string $manufacturer,
        string $modelName,
        int $maxRangeKm
    ): AircraftModel {
        $model = $this->createAircraftModelObject($manufacturer, $modelName, $maxRangeKm);

        $this->requestCheckerService->validateRequestDataByConstraints($model);

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $model;
    }

    private function createAircraftModelObject(
        string $manufacturer,
        string $modelName,
        int $maxRangeKm
    ): AircraftModel {
        $model = new AircraftModel();
        $model->setManufacturer($manufacturer);
        $model->setModelName($modelName);
        $model->setMaxRangeKm($maxRangeKm);

        return $model;
    }

    public function updateAircraftModel(AircraftModel $model, array $data): void
    {
        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($model, $method)) {
                $model->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($model);
        
        $this->entityManager->flush();
    }
}