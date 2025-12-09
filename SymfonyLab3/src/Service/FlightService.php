<?php

namespace App\Service;

use App\Entity\Flight;
use App\Entity\Airport;
use App\Entity\Aircraft;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class FlightService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createFlight(
        string $flightNumber,
        int $departureAirportId,
        int $arrivalAirportId,
        string $departureTime,
        string $arrivalTime,
        string $basePrice,
        ?string $status,
        ?int $aircraftId
    ): Flight {
        $depAirport = $this->entityManager->getRepository(Airport::class)->find($departureAirportId);
        if (!$depAirport) {
            throw new NotFoundHttpException('Departure airport not found with id ' . $departureAirportId);
        }

        $arrAirport = $this->entityManager->getRepository(Airport::class)->find($arrivalAirportId);
        if (!$arrAirport) {
            throw new NotFoundHttpException('Arrival airport not found with id ' . $arrivalAirportId);
        }

        $aircraft = null;
        if ($aircraftId) {
            $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($aircraftId);
            if (!$aircraft) {
                throw new NotFoundHttpException('Aircraft not found with id ' . $aircraftId);
            }
        }

        $flight = $this->createFlightObject(
            $flightNumber,
            $depAirport,
            $arrAirport,
            $departureTime,
            $arrivalTime,
            $basePrice,
            $status,
            $aircraft
        );

        $this->requestCheckerService->validateRequestDataByConstraints($flight);

        $this->entityManager->persist($flight);
        $this->entityManager->flush();

        return $flight;
    }

    private function createFlightObject(
        string $flightNumber,
        Airport $depAirport,
        Airport $arrAirport,
        string $departureTime,
        string $arrivalTime,
        string $basePrice,
        ?string $status,
        ?Aircraft $aircraft
    ): Flight {
        $flight = new Flight();
        $flight->setFlightNumber($flightNumber);
        $flight->setDepartureAirport($depAirport);
        $flight->setArrivalAirport($arrAirport);
        $flight->setBasePrice($basePrice);
        
        if ($status) {
            $flight->setStatus($status);
        }
        
        if ($aircraft) {
            $flight->setAircraft($aircraft);
        }

        try {
            $flight->setDepartureTime(new \DateTime($departureTime));
            $flight->setArrivalTime(new \DateTime($arrivalTime));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format');
        }

        return $flight;
    }

    public function updateFlight(Flight $flight, array $data): void
    {
        if (array_key_exists('departure_airport_id', $data)) {
            $airport = $this->entityManager->getRepository(Airport::class)->find($data['departure_airport_id']);
            if (!$airport) throw new NotFoundHttpException('Departure airport not found');
            $flight->setDepartureAirport($airport);
            unset($data['departure_airport_id']);
        }

        if (array_key_exists('arrival_airport_id', $data)) {
            $airport = $this->entityManager->getRepository(Airport::class)->find($data['arrival_airport_id']);
            if (!$airport) throw new NotFoundHttpException('Arrival airport not found');
            $flight->setArrivalAirport($airport);
            unset($data['arrival_airport_id']);
        }

        if (array_key_exists('aircraft_id', $data)) {
            if ($data['aircraft_id']) {
                $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($data['aircraft_id']);
                if (!$aircraft) throw new NotFoundHttpException('Aircraft not found');
                $flight->setAircraft($aircraft);
            } else {
                $flight->setAircraft(null);
            }
            unset($data['aircraft_id']);
        }

        if (array_key_exists('departure_time', $data)) {
            try {
                $flight->setDepartureTime(new \DateTime($data['departure_time']));
            } catch (\Exception $e) { throw new \InvalidArgumentException('Invalid departure_time format'); }
            unset($data['departure_time']);
        }
        if (array_key_exists('arrival_time', $data)) {
            try {
                $flight->setArrivalTime(new \DateTime($data['arrival_time']));
            } catch (\Exception $e) { throw new \InvalidArgumentException('Invalid arrival_time format'); }
            unset($data['arrival_time']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            if (method_exists($flight, $method)) {
                $flight->$method((string)$value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($flight);
        
        $this->entityManager->flush();
    }
}