<?php

namespace App\Controller;

use App\Entity\Airport;
use App\Entity\Country;
use App\Service\AirportService;
use App\Service\RequestValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/airports')]
class AirportController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AirportService $airportService,        
        private RequestValidatorService $validator      
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $airports = $this->entityManager->getRepository(Airport::class)->findAll();
        
        $data = [];
        foreach ($airports as $airport) {
            $data[] = [
                'id' => $airport->getId(),
                'name' => $airport->getName(),
                'iata_code' => $airport->getIataCode(),
                'city' => $airport->getCity(),
                'country' => [
                    'id' => $airport->getCountry()->getId(),
                    'name' => $airport->getCountry()->getName(),
                    'code' => $airport->getCountry()->getCode(),
                ]
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], 404);
        }

        return $this->json([
            'id' => $airport->getId(),
            'name' => $airport->getName(),
            'iata_code' => $airport->getIataCode(),
            'city' => $airport->getCity(),
            'country' => [
                'id' => $airport->getCountry()->getId(),
                'name' => $airport->getCountry()->getName(),
            ]
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $requiredFields = ['country_id', 'name', 'iata_code'];
            $this->validator->validateRequiredFields($data, $requiredFields);

            $airport = $this->airportService->createAirport($data);

            return $this->json(['status' => 'Created', 'id' => $airport->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $airport->setName($data['name']);
        }
        if (isset($data['iata_code'])) {
            $airport->setIataCode($data['iata_code']);
        }
        if (isset($data['city'])) {
            $airport->setCity($data['city']);
        }

        if (isset($data['country_id'])) {
            $country = $this->entityManager->getRepository(Country::class)->find($data['country_id']);
            if ($country) {
                $airport->setCountry($country);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $airport->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $airport = $this->entityManager->getRepository(Airport::class)->find($id);

        if (!$airport) {
            return $this->json(['error' => 'Airport not found'], 404);
        }

        $this->entityManager->remove($airport);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}