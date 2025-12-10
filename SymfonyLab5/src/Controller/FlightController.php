<?php

namespace App\Controller;

use App\Entity\Flight;
use App\Repository\FlightRepository; 
use App\Service\FlightService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/flights')]
class FlightController extends AbstractController
{
    private const REQUIRED_FIELDS = [
        'flight_number', 
        'departure_airport_id', 
        'arrival_airport_id', 
        'departure_time', 
        'arrival_time', 
        'base_price'
    ];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private FlightService $flightService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Flight::class);

        $result = $repository->getAllFlightsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($flight);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $flight = $this->flightService->createFlight(
                $data['flight_number'],
                (int)$data['departure_airport_id'],
                (int)$data['arrival_airport_id'],
                $data['departure_time'],
                $data['arrival_time'],
                (string)$data['base_price'],
                $data['status'] ?? null,
                isset($data['aircraft_id']) ? (int)$data['aircraft_id'] : null
            );

            $this->entityManager->flush();

            return $this->json($flight, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->flightService->updateFlight($flight, $data);
            
            $this->entityManager->flush();

            return $this->json($flight);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($flight);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete flight because it has linked tickets'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}