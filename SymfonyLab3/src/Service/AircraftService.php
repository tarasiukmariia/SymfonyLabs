<?php

namespace App\Service;

use App\Entity\Aircraft;
use App\Entity\AircraftModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AircraftService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}


    public function createAircraft(
        string $registrationNumber,
        int $totalCapacity,
        int $modelId,
        ?string $manufactureDate
    ): Aircraft {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($modelId);
        if (!$model) {
            throw new NotFoundHttpException('Aircraft Model not found with id ' . $modelId);
        }

        $aircraft = $this->createAircraftObject($registrationNumber, $totalCapacity, $model, $manufactureDate);

        $this->requestCheckerService->validateRequestDataByConstraints($aircraft);

        $this->entityManager->persist($aircraft);
        $this->entityManager->flush();

        return $aircraft;
    }

    private function createAircraftObject(
        string $registrationNumber,
        int $totalCapacity,
        AircraftModel $model,
        ?string $manufactureDate
    ): Aircraft {
        $aircraft = new Aircraft();
        $aircraft->setRegistrationNumber($registrationNumber);
        $aircraft->setTotalCapacity($totalCapacity);
        $aircraft->setModel($model);

        if ($manufactureDate) {
            try {
                $aircraft->setManufactureDate(new \DateTime($manufactureDate));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException("Invalid date format for manufacture_date");
            }
        }

        return $aircraft;
    }

    public function updateAircraft(Aircraft $aircraft, array $data): void
    {
        if (array_key_exists('model_id', $data)) {
            $model = $this->entityManager->getRepository(AircraftModel::class)->find($data['model_id']);
            if (!$model) {
                throw new NotFoundHttpException('Aircraft Model not found');
            }
            $aircraft->setModel($model);
            unset($data['model_id']); 
        }

        if (array_key_exists('manufacture_date', $data)) {
            if ($data['manufacture_date']) {
                try {
                    $aircraft->setManufactureDate(new \DateTime($data['manufacture_date']));
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException("Invalid date format");
                }
            } else {
                $aircraft->setManufactureDate(null);
            }
            unset($data['manufacture_date']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($aircraft, $method)) {
                $aircraft->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($aircraft);
        
        $this->entityManager->flush();
    }
}