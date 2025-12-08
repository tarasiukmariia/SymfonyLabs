<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\TravelClass;
use App\Service\RequestValidatorService;
use App\Service\TicketService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/tickets')]
class TicketController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TicketService $ticketService,          
        private RequestValidatorService $validator      
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tickets = $this->entityManager->getRepository(Ticket::class)->findAll();
        
        $data = [];
        foreach ($tickets as $ticket) {
            $data[] = [
                'id' => $ticket->getId(),
                'seat_number' => $ticket->getSeatNumber(),
                'price' => $ticket->getPrice(),
                'booking_ref' => $ticket->getBooking()->getBookingReference(),
                'flight_number' => $ticket->getFlight()->getFlightNumber(),
                'passenger_name' => $ticket->getPassenger()->getFirstName() . ' ' . $ticket->getPassenger()->getLastName(),
                'class' => $ticket->getTravelClass()->getName(), 
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], 404);
        }

        return $this->json([
            'id' => $ticket->getId(),
            'seat_number' => $ticket->getSeatNumber(),
            'price' => $ticket->getPrice(),
            'booking_id' => $ticket->getBooking()->getId(),
            'flight_id' => $ticket->getFlight()->getId(),
            'passenger_id' => $ticket->getPassenger()->getId(),
            'travel_class_id' => $ticket->getTravelClass()->getId(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $required = ['booking_id', 'flight_id', 'passenger_id', 'travel_class_id', 'price'];
            $this->validator->validateRequiredFields($data, $required);

            $ticket = $this->ticketService->createTicket($data);

            return $this->json(['status' => 'Created', 'id' => $ticket->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['seat_number'])) $ticket->setSeatNumber($data['seat_number']);
        if (isset($data['price'])) $ticket->setPrice((string)$data['price']);

        if (isset($data['flight_id'])) {
            $flight = $this->entityManager->getRepository(Flight::class)->find($data['flight_id']);
            if ($flight) $ticket->setFlight($flight);
        }
        
        if (isset($data['booking_id'])) {
            $booking = $this->entityManager->getRepository(Booking::class)->find($data['booking_id']);
            if ($booking) $ticket->setBooking($booking);
        }

        if (isset($data['travel_class_id'])) {
            $travelClass = $this->entityManager->getRepository(TravelClass::class)->find($data['travel_class_id']);
            if ($travelClass) $ticket->setTravelClass($travelClass);
        }
        
        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $ticket->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($id);

        if (!$ticket) {
            return $this->json(['error' => 'Ticket not found'], 404);
        }

        $this->entityManager->remove($ticket);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}