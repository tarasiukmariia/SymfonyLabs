<?php

namespace App\Controller;

use App\Entity\TravelClass;
use App\Repository\TravelClassRepository; 
use App\Service\RequestCheckerService;
use App\Service\TravelClassService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/travel-classes')]
class TravelClassController extends AbstractController
{
    private const REQUIRED_FIELDS = ['name', 'price_multiplier'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TravelClassService $travelClassService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(TravelClass::class);

        $result = $repository->getAllTravelClassesByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($class);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $class = $this->travelClassService->createTravelClass(
                $data['name'],
                (string)$data['price_multiplier']
            );

            $this->entityManager->flush();

            return $this->json($class, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->travelClassService->updateTravelClass($class, $data);
            
            $this->entityManager->flush();

            return $this->json($class);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($class);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete this class because it is used in tickets'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}