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
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createTicket(array $data): Ticket
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($data['booking_id']);
        if (!$booking) {
            throw new NotFoundHttpException('Booking not found');
        }

        $flight = $this->entityManager->getRepository(Flight::class)->find($data['flight_id']);
        if (!$flight) {
            throw new NotFoundHttpException('Flight not found');
        }

        $passenger = $this->entityManager->getRepository(Passenger::class)->find($data['passenger_id']);
        if (!$passenger) {
            throw new NotFoundHttpException('Passenger not found');
        }

        $travelClass = $this->entityManager->getRepository(TravelClass::class)->find($data['travel_class_id']);
        if (!$travelClass) {
            throw new NotFoundHttpException('Travel Class not found');
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

        return $ticket;
    }
}