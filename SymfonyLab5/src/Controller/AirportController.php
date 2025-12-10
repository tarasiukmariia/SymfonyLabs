<?php

namespace App\Controller;

use App\Entity\Airport;
use App\Repository\AirportRepository; 
use App\Service\AirportService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/airports')]
class AirportController extends AbstractController
{
    private const REQUIRED_FIELDS = ['name', 'iata_code', 'country_id', 'city'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AirportService $airportService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Airport::class);

        $result = $repository->getAllAirportsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($airport);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $airport = $this->airportService->createAirport(
                $data['name'],
                $data['iata_code'],
                (int)$data['country_id'],
                $data['city']
            );

            $this->entityManager->flush();

            return $this->json($airport, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->airportService->updateAirport($airport, $data);
            
            $this->entityManager->flush();

            return $this->json($airport);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($airport);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete airport because it is linked to flights'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}