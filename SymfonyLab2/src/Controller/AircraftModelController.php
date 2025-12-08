<?php

namespace App\Controller;

use App\Entity\AircraftModel;
use App\Service\AircraftModelService;
use App\Service\RequestValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/aircraft-models')]
class AircraftModelController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AircraftModelService $aircraftModelService, 
        private RequestValidatorService $validator         
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $models = $this->entityManager->getRepository(AircraftModel::class)->findAll();
        
        $data = [];
        foreach ($models as $model) {
            $data[] = [
                'id' => $model->getId(),
                'manufacturer' => $model->getManufacturer(),
                'model_name' => $model->getModelName(),
                'max_range_km' => $model->getMaxRangeKm(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], 404);
        }

        return $this->json([
            'id' => $model->getId(),
            'manufacturer' => $model->getManufacturer(),
            'model_name' => $model->getModelName(),
            'max_range_km' => $model->getMaxRangeKm(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $requiredFields = ['manufacturer', 'model_name', 'max_range_km'];
            $this->validator->validateRequiredFields($data, $requiredFields);

            $model = $this->aircraftModelService->createAircraftModel($data);

            return $this->json(['status' => 'Created', 'id' => $model->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['manufacturer'])) {
            $model->setManufacturer($data['manufacturer']);
        }
        if (isset($data['model_name'])) {
            $model->setModelName($data['model_name']);
        }
        if (isset($data['max_range_km'])) {
            $model->setMaxRangeKm((int)$data['max_range_km']);
        }

        $this->entityManager->flush();

        return $this->json([
            'status' => 'Updated',
            'id' => $model->getId(),
            'manufacturer' => $model->getManufacturer(),
            'model_name' => $model->getModelName(),
            'max_range_km' => $model->getMaxRangeKm()
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], 404);
        }

        $this->entityManager->remove($model);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}