<?php

namespace App\Controller;

use App\Entity\Passenger;
use App\Repository\PassengerRepository;
use App\Service\PassengerService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/passengers')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class PassengerController extends AbstractController
{
    private const REQUIRED_FIELDS = ['first_name', 'last_name', 'email', 'passport_number', 'date_of_birth'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private PassengerService $passengerService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_passengers_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Passenger::class);

        $result = $repository->getAllPassengersByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_passengers_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return new JsonResponse(['error' => 'Passenger not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($passenger, Response::HTTP_OK);
    }

    #[Route('', name: 'app_passengers_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $passenger = $this->passengerService->createPassenger(
                $data['first_name'],
                $data['last_name'],
                $data['email'],
                $data['passport_number'],
                $data['date_of_birth'],
                $data['phone'] ?? null
            );

            $this->entityManager->flush();

            return new JsonResponse($passenger, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_passengers_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return new JsonResponse(['error' => 'Passenger not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->passengerService->updatePassenger($passenger, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($passenger, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_passengers_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return new JsonResponse(['error' => 'Passenger not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($passenger);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete passenger because they have linked bookings/tickets'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}