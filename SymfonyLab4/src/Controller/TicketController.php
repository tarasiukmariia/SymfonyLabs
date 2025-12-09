<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Repository\TicketRepository; 
use App\Service\RequestCheckerService;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/tickets')]
class TicketController extends AbstractController
{
    private const REQUIRED_FIELDS = ['booking_id', 'flight_id', 'passenger_id', 'travel_class_id', 'price'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TicketService $ticketService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Ticket::class);

        $result = $repository->getAllTicketsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($ticket);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $ticket = $this->ticketService->createTicket(
                (int)$data['booking_id'],
                (int)$data['flight_id'],
                (int)$data['passenger_id'],
                (int)$data['travel_class_id'],
                (string)$data['price'],
                $data['seat_number'] ?? null
            );

            $this->entityManager->flush();

            return $this->json($ticket, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->ticketService->updateTicket($ticket, $data);
            
            $this->entityManager->flush();

            return $this->json($ticket);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($ticket);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete this class because it is used in tickets'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}