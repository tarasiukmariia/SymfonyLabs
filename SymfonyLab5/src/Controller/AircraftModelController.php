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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/aircraft-models')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AircraftModelController extends AbstractController
{
    private const REQUIRED_FIELDS = ['manufacturer', 'model_name', 'max_range_km'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AircraftModelService $aircraftModelService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_aircraft_models_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(AircraftModel::class);

        $result = $repository->getAllAircraftModelsByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_aircraft_models_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($model, Response::HTTP_OK);
    }

    #[Route('', name: 'app_aircraft_models_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $model = $this->aircraftModelService->createAircraftModel(
                $data['manufacturer'],
                $data['model_name'],
                (int)$data['max_range_km']
            );

            $this->entityManager->flush();

            return new JsonResponse($model, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_aircraft_models_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->aircraftModelService->updateAircraftModel($model, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($model, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_aircraft_models_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $model = $this->entityManager->getRepository(AircraftModel::class)->find($id);

        if (!$model) {
            return new JsonResponse(['error' => 'Model not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($model);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete model because it is used by aircrafts'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}