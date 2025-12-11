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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/flights')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
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

    #[Route('', name: 'app_flights_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Flight::class);

        $result = $repository->getAllFlightsByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_flights_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return new JsonResponse(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($flight, Response::HTTP_OK);
    }

    #[Route('', name: 'app_flights_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

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

            return new JsonResponse($flight, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_flights_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return new JsonResponse(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->flightService->updateFlight($flight, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($flight, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_flights_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return new JsonResponse(['error' => 'Flight not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($flight);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete flight because it has linked tickets'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}