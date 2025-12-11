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
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/tickets')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class TicketController extends AbstractController
{
    private const REQUIRED_FIELDS = ['booking_id', 'flight_id', 'passenger_id', 'travel_class_id', 'price'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TicketService $ticketService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_tickets_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Ticket::class);

        $result = $repository->getAllTicketsByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_tickets_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return new JsonResponse(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($ticket, Response::HTTP_OK);
    }

    #[Route('', name: 'app_tickets_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $ticket = $this->ticketService->createTicket(
                (int)$data['booking_id'],
                (int)$data['flight_id'],
                (int)$data['passenger_id'],
                (int)$data['travel_class_id'],
                (string)$data['price'],
                $data['seat_number'] ?? null
            );

            $this->entityManager->flush();

            return new JsonResponse($ticket, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_tickets_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return new JsonResponse(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->ticketService->updateTicket($ticket, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($ticket, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_tickets_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return new JsonResponse(['error' => 'Ticket not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($ticket);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete this class because it is used in tickets'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}