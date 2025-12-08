<?php

namespace App\Controller;

use App\Entity\Baggage;
use App\Entity\Ticket;
use App\Service\BaggageService;
use App\Service\RequestValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/baggage')]
class BaggageController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private BaggageService $baggageService,         
        private RequestValidatorService $validator      
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $baggageList = $this->entityManager->getRepository(Baggage::class)->findAll();
        
        $data = [];
        foreach ($baggageList as $item) {
            $data[] = [
                'id' => $item->getId(),
                'weight_kg' => $item->getWeightKg(),
                'type' => $item->getType(),
                'price' => $item->getPrice(),
                'ticket_id' => $item->getTicket()->getId() 
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$item) {
            return $this->json(['error' => 'Baggage item not found'], 404);
        }

        return $this->json([
            'id' => $item->getId(),
            'weight_kg' => $item->getWeightKg(),
            'type' => $item->getType(),
            'price' => $item->getPrice(),
            'ticket_id' => $item->getTicket()->getId()
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            // 1. ВАЛІДАЦІЯ
            $requiredFields = ['ticket_id', 'weight_kg'];
            $this->validator->validateRequiredFields($data, $requiredFields);

            // 2. ЛОГІКА (через сервіс)
            $baggage = $this->baggageService->createBaggage($data);

            // 3. ВІДПОВІДЬ
            return $this->json(['status' => 'Created', 'id' => $baggage->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $baggage = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$baggage) {
            return $this->json(['error' => 'Baggage item not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['weight_kg'])) {
            $baggage->setWeightKg((string)$data['weight_kg']);
        }
        if (isset($data['type'])) {
            $baggage->setType($data['type']);
        }
        if (isset($data['price'])) {
            $baggage->setPrice((string)$data['price']);
        }
        
        if (isset($data['ticket_id'])) {
            $ticket = $this->entityManager->getRepository(Ticket::class)->find($data['ticket_id']);
            if ($ticket) {
                $baggage->setTicket($ticket);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $baggage->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $baggage = $this->entityManager->getRepository(Baggage::class)->find($id);

        if (!$baggage) {
            return $this->json(['error' => 'Baggage item not found'], 404);
        }

        $this->entityManager->remove($baggage);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}