<?php

namespace App\Controller;

use App\Entity\Ticket;
use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\Passenger;
use App\Entity\TravelClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tickets')]
class TicketController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

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

        $required = ['booking_id', 'flight_id', 'passenger_id', 'travel_class_id', 'price'];
        foreach ($required as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing required field: $field"], 400);
            }
        }

        $booking = $this->entityManager->getRepository(Booking::class)->find($data['booking_id']);
        $flight = $this->entityManager->getRepository(Flight::class)->find($data['flight_id']);
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($data['passenger_id']);
        $travelClass = $this->entityManager->getRepository(TravelClass::class)->find($data['travel_class_id']);

        if (!$booking || !$flight || !$passenger || !$travelClass) {
            return $this->json(['error' => 'One of the related entities (Booking, Flight, Passenger, TravelClass) not found'], 404);
        }

        $ticket = new Ticket();
        $ticket->setBooking($booking);
        $ticket->setFlight($flight);
        $ticket->setPassenger($passenger);
        $ticket->setTravelClass($travelClass);
        $ticket->setPrice((string)$data['price']);
        
        if (isset($data['seat_number'])) {
            $ticket->setSeatNumber($data['seat_number']);
        }

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $ticket->getId()], 201);
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