<?php

namespace App\Controller;

use App\Entity\Flight;
use App\Entity\Airport;
use App\Entity\Aircraft;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/flights')]
class FlightController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $flights = $this->entityManager->getRepository(Flight::class)->findAll();
        
        $data = [];
        foreach ($flights as $flight) {
            $data[] = [
                'id' => $flight->getId(),
                'flight_number' => $flight->getFlightNumber(),
                'status' => $flight->getStatus(),
                'departure_time' => $flight->getDepartureTime()->format('Y-m-d H:i:s'),
                'arrival_time' => $flight->getArrivalTime()->format('Y-m-d H:i:s'),
                'base_price' => $flight->getBasePrice(),
                'departure_airport' => $flight->getDepartureAirport()->getIataCode(), 
                'arrival_airport' => $flight->getArrivalAirport()->getIataCode(),    
                'aircraft' => $flight->getAircraft() ? $flight->getAircraft()->getRegistrationNumber() : null
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], 404);
        }

        return $this->json([
            'id' => $flight->getId(),
            'flight_number' => $flight->getFlightNumber(),
            'status' => $flight->getStatus(),
            'departure_time' => $flight->getDepartureTime()->format('Y-m-d H:i:s'),
            'arrival_time' => $flight->getArrivalTime()->format('Y-m-d H:i:s'),
            'base_price' => $flight->getBasePrice(),
            'departure_airport_id' => $flight->getDepartureAirport()->getId(),
            'arrival_airport_id' => $flight->getArrivalAirport()->getId(),
            'aircraft_id' => $flight->getAircraft() ? $flight->getAircraft()->getId() : null
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['flight_number', 'departure_airport_id', 'arrival_airport_id', 'departure_time', 'arrival_time', 'base_price'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return $this->json(['error' => "Missing field: $field"], 400);
            }
        }

        $depAirport = $this->entityManager->getRepository(Airport::class)->find($data['departure_airport_id']);
        $arrAirport = $this->entityManager->getRepository(Airport::class)->find($data['arrival_airport_id']);

        if (!$depAirport || !$arrAirport) {
            return $this->json(['error' => 'One of the airports not found'], 404);
        }

        $flight = new Flight();
        $flight->setFlightNumber($data['flight_number']);
        $flight->setDepartureAirport($depAirport);
        $flight->setArrivalAirport($arrAirport);
        $flight->setDepartureTime(new \DateTime($data['departure_time']));
        $flight->setArrivalTime(new \DateTime($data['arrival_time']));
        $flight->setBasePrice((string)$data['base_price']);
        $flight->setStatus($data['status'] ?? 'Scheduled');

        if (isset($data['aircraft_id'])) {
            $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($data['aircraft_id']);
            if ($aircraft) {
                $flight->setAircraft($aircraft);
            } else {
                return $this->json(['error' => 'Aircraft not found'], 404);
            }
        }

        $this->entityManager->persist($flight);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $flight->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['flight_number'])) $flight->setFlightNumber($data['flight_number']);
        if (isset($data['status'])) $flight->setStatus($data['status']);
        if (isset($data['base_price'])) $flight->setBasePrice((string)$data['base_price']);
        if (isset($data['departure_time'])) $flight->setDepartureTime(new \DateTime($data['departure_time']));
        if (isset($data['arrival_time'])) $flight->setArrivalTime(new \DateTime($data['arrival_time']));

        if (isset($data['aircraft_id'])) {
            $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($data['aircraft_id']);
            if ($aircraft) $flight->setAircraft($aircraft);
        }

        if (isset($data['departure_airport_id'])) {
            $airport = $this->entityManager->getRepository(Airport::class)->find($data['departure_airport_id']);
            if ($airport) $flight->setDepartureAirport($airport);
        }
        
        if (isset($data['arrival_airport_id'])) {
            $airport = $this->entityManager->getRepository(Airport::class)->find($data['arrival_airport_id']);
            if ($airport) $flight->setArrivalAirport($airport);
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $flight->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $flight = $this->entityManager->getRepository(Flight::class)->find($id);

        if (!$flight) {
            return $this->json(['error' => 'Flight not found'], 404);
        }

        $this->entityManager->remove($flight);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}