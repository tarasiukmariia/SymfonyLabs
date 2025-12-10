<?php

namespace App\Controller;

use App\Entity\AircraftModel;
use App\Repository\AircraftModelRepository; 
use App\Service\AircraftModelService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/aircraft-models')]
class AircraftModelController extends AbstractController
{
    private const REQUIRED_FIELDS = ['manufacturer', 'model_name', 'max_range_km'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AircraftModelService $aircraftModelService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(AircraftModel::class);

        $result = $repository->getAllAircraftModelsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($model);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $model = $this->aircraftModelService->createAircraftModel(
                $data['manufacturer'],
                $data['model_name'],
                (int)$data['max_range_km']
            );

            $this->entityManager->flush();

            return $this->json($model, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->aircraftModelService->updateAircraftModel($model, $data);
            
            $this->entityManager->flush();

            return $this->json($model);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return $this->json(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($model);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete model because it is used by aircrafts'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}