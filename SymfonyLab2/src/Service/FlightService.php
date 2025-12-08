<?php

namespace App\Service;

use App\Entity\Flight;
use App\Entity\Airport;
use App\Entity\Aircraft;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FlightService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createFlight(array $data): Flight
    {
        $depAirport = $this->entityManager->getRepository(Airport::class)->find($data['departure_airport_id']);
        $arrAirport = $this->entityManager->getRepository(Airport::class)->find($data['arrival_airport_id']);

        if (!$depAirport) {
            throw new NotFoundHttpException('Departure airport not found');
        }
        if (!$arrAirport) {
            throw new NotFoundHttpException('Arrival airport not found');
        }

        $flight = new Flight();
        $flight->setFlightNumber($data['flight_number']);
        $flight->setDepartureAirport($depAirport);
        $flight->setArrivalAirport($arrAirport);
        $flight->setDepartureTime(new \DateTime($data['departure_time']));
        $flight->setArrivalTime(new \DateTime($data['arrival_time']));
        $flight->setBasePrice((string)$data['base_price']);
        
        if (isset($data['status'])) {
            $flight->setStatus($data['status']);
        }

        if (isset($data['aircraft_id'])) {
            $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($data['aircraft_id']);
            if (!$aircraft) {
                throw new NotFoundHttpException('Aircraft not found');
            }
            $flight->setAircraft($aircraft);
        }

        $this->entityManager->persist($flight);
        $this->entityManager->flush();

        return $flight;
    }
}