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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/airports')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class AirportController extends AbstractController
{
    private const REQUIRED_FIELDS = ['name', 'iata_code', 'country_id', 'city'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AirportService $airportService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_airports_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Airport::class);

        $result = $repository->getAllAirportsByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_airports_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return new JsonResponse(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($airport, Response::HTTP_OK);
    }

    #[Route('', name: 'app_airports_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $airport = $this->airportService->createAirport(
                $data['name'],
                $data['iata_code'],
                (int)$data['country_id'],
                $data['city']
            );

            $this->entityManager->flush();

            return new JsonResponse($airport, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_airports_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return new JsonResponse(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->airportService->updateAirport($airport, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($airport, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_airports_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return new JsonResponse(['error' => 'Airport not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($airport);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete airport because it is linked to flights'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}