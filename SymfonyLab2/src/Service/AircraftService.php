<?php

namespace App\Service;

use App\Entity\Aircraft;
use App\Entity\AircraftModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AircraftService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createAircraft(array $data): Aircraft
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($data['model_id']);
        
        if (!$model) {
            throw new NotFoundHttpException('Model not found');
        }

        $aircraft = new Aircraft();
        $aircraft->setRegistrationNumber($data['registration_number']);
        $aircraft->setTotalCapacity((int)$data['total_capacity']);
        $aircraft->setModel($model);
        
        if (isset($data['manufacture_date'])) {
            try {
                $aircraft->setManufactureDate(new \DateTime($data['manufacture_date']));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format for manufacture_date');
            }
        }

        $this->entityManager->persist($aircraft);
        $this->entityManager->flush();

        return $aircraft;
    }
}