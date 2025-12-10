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
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/aircrafts')]
class AircraftController extends AbstractController
{
    private const REQUIRED_FIELDS = ['model_id', 'registration_number', 'total_capacity'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AircraftService $aircraftService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();

        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Aircraft::class);

        $result = $repository->getAllAircraftsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($aircraft);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $aircraft = $this->aircraftService->createAircraft(
                $data['registration_number'],
                (int)$data['total_capacity'],
                (int)$data['model_id'],
                $data['manufacture_date'] ?? null
            );

            $this->entityManager->flush();

            return $this->json($aircraft, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->aircraftService->updateAircraft($aircraft, $data);
            
            $this->entityManager->flush();

            return $this->json($aircraft);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($aircraft);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete aircraft because it is linked to flights'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}