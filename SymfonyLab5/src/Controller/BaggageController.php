<?php

namespace App\Controller;

use App\Entity\Baggage;
use App\Repository\BaggageRepository;
use App\Service\BaggageService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/baggage')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class BaggageController extends AbstractController
{
    private const REQUIRED_FIELDS = ['ticket_id', 'weight_kg'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BaggageService $baggageService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_baggage_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Baggage::class);

        $result = $repository->getAllBaggageByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_baggage_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$item) {
            return new JsonResponse(['error' => 'Baggage item not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($item, Response::HTTP_OK);
    }

    #[Route('', name: 'app_baggage_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $baggage = $this->baggageService->createBaggage(
                (int)$data['ticket_id'],
                (string)$data['weight_kg'],
                $data['type'] ?? 'checked',
                isset($data['price']) ? (string)$data['price'] : null
            );

            $this->entityManager->flush();

            return new JsonResponse($baggage, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_baggage_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $baggage = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$baggage) {
            return new JsonResponse(['error' => 'Baggage item not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->baggageService->updateBaggage($baggage, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($baggage, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_baggage_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $baggage = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$baggage) {
            return new JsonResponse(['error' => 'Baggage item not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($baggage);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete baggage item'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}