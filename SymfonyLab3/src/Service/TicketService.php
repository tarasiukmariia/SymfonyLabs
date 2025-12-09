<?php

namespace App\Service;

use App\Entity\Ticket;
use App\Entity\Booking;
use App\Entity\Flight;
use App\Entity\Passenger;
use App\Entity\TravelClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TicketService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createTicket(
        int $bookingId,
        int $flightId,
        int $passengerId,
        int $travelClassId,
        string $price,
        ?string $seatNumber
    ): Ticket {
        $booking = $this->entityManager->getRepository(Booking::class)->find($bookingId);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found with id ' . $bookingId);
        }

        $flight = $this->entityManager->getRepository(Flight::class)->find($flightId);
        if (!$flight) {
            throw new NotFoundHttpException('Flight not found with id ' . $flightId);
        }

        $passenger = $this->entityManager->getRepository(Passenger::class)->find($passengerId);
        if (!$passenger) {
            throw new NotFoundHttpException('Passenger not found with id ' . $passengerId);
        }

        $travelClass = $this->entityManager->getRepository(TravelClass::class)->find($travelClassId);
        if (!$travelClass) {
            throw new NotFoundHttpException('Travel Class not found with id ' . $travelClassId);
        }

        $ticket = $this->createTicketObject(
            $booking,
            $flight,
            $passenger,
            $travelClass,
            $price,
            $seatNumber
        );

        $this->requestCheckerService->validateRequestDataByConstraints($ticket);

        $this->entityManager->persist($ticket);
        $this->entityManager->flush();

        return $ticket;
    }

    private function createTicketObject(
        Booking $booking,
        Flight $flight,
        Passenger $passenger,
        TravelClass $travelClass,
        string $price,
        ?string $seatNumber
    ): Ticket {
        $ticket = new Ticket();
        $ticket->setBooking($booking);
        $ticket->setFlight($flight);
        $ticket->setPassenger($passenger);
        $ticket->setTravelClass($travelClass);
        $ticket->setPrice($price);
        
        if ($seatNumber) {
            $ticket->setSeatNumber($seatNumber);
        }

        return $ticket;
    }

    public function updateTicket(Ticket $ticket, array $data): void
    {
        if (array_key_exists('booking_id', $data)) {
            $booking = $this->entityManager->getRepository(Booking::class)->find($data['booking_id']);
            if (!$booking) throw new NotFoundHttpException('Booking not found');
            $ticket->setBooking($booking);
            unset($data['booking_id']);
        }

        if (array_key_exists('flight_id', $data)) {
            $flight = $this->entityManager->getRepository(Flight::class)->find($data['flight_id']);
            if (!$flight) throw new NotFoundHttpException('Flight not found');
            $ticket->setFlight($flight);
            unset($data['flight_id']);
        }

        if (array_key_exists('passenger_id', $data)) {
            $passenger = $this->entityManager->getRepository(Passenger::class)->find($data['passenger_id']);
            if (!$passenger) throw new NotFoundHttpException('Passenger not found');
            $ticket->setPassenger($passenger);
            unset($data['passenger_id']);
        }

        if (array_key_exists('travel_class_id', $data)) {
            $travelClass = $this->entityManager->getRepository(TravelClass::class)->find($data['travel_class_id']);
            if (!$travelClass) throw new NotFoundHttpException('Travel Class not found');
            $ticket->setTravelClass($travelClass);
            unset($data['travel_class_id']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($ticket, $method)) {
                $ticket->$method((string)$value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($ticket);
        
        $this->entityManager->flush();
    }
}