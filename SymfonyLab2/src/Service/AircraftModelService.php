<?php

namespace App\Service;

use App\Entity\AircraftModel;
use Doctrine\ORM\EntityManagerInterface;

class AircraftModelService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createAircraftModel(array $data): AircraftModel
    {
        $model = new AircraftModel();
        $model->setManufacturer($data['manufacturer']);
        $model->setModelName($data['model_name']);
        $model->setMaxRangeKm((int)$data['max_range_km']);

        $this->entityManager->persist($model);
        $this->entityManager->flush();

        return $model;
    }
}