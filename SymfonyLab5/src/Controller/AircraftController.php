<?php

namespace App\Controller;

use App\Entity\Aircraft;
use App\Repository\AircraftRepository;
use App\Service\AircraftService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/aircrafts')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AircraftController extends AbstractController
{
    private const REQUIRED_FIELDS = ['model_id', 'registration_number', 'total_capacity'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AircraftService $aircraftService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_aircrafts_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Aircraft::class);

        $result = $repository->getAllAircraftsByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_aircrafts_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return new JsonResponse(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($aircraft, Response::HTTP_OK);
    }

    #[Route('', name: 'app_aircrafts_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $aircraft = $this->aircraftService->createAircraft(
                $data['registration_number'],
                (int)$data['total_capacity'],
                (int)$data['model_id'],
                $data['manufacture_date'] ?? null
            );

            $this->entityManager->flush();

            return new JsonResponse($aircraft, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_aircrafts_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return new JsonResponse(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->aircraftService->updateAircraft($aircraft, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($aircraft, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_aircrafts_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return new JsonResponse(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($aircraft);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete aircraft because it is linked to flights'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}