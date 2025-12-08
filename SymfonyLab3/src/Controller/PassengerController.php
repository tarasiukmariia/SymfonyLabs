<?php

namespace App\Controller;

use App\Entity\Passenger;
use App\Service\PassengerService;
use App\Service\RequestValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/passengers')]
class PassengerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private PassengerService $passengerService,     
        private RequestValidatorService $validator      
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $passengers = $this->entityManager->getRepository(Passenger::class)->findAll();
        
        $data = [];
        foreach ($passengers as $passenger) {
            $data[] = [
                'id' => $passenger->getId(),
                'first_name' => $passenger->getFirstName(),
                'last_name' => $passenger->getLastName(),
                'email' => $passenger->getEmail(),
                'phone' => $passenger->getPhone(),
                'passport_number' => $passenger->getPassportNumber(),
                'date_of_birth' => $passenger->getDateOfBirth()->format('Y-m-d'),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return $this->json(['error' => 'Passenger not found'], 404);
        }

        return $this->json([
            'id' => $passenger->getId(),
            'first_name' => $passenger->getFirstName(),
            'last_name' => $passenger->getLastName(),
            'email' => $passenger->getEmail(),
            'phone' => $passenger->getPhone(),
            'passport_number' => $passenger->getPassportNumber(),
            'date_of_birth' => $passenger->getDateOfBirth()->format('Y-m-d'),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $requiredFields = ['first_name', 'last_name', 'email', 'passport_number', 'date_of_birth'];
            $this->validator->validateRequiredFields($data, $requiredFields);

            $passenger = $this->passengerService->createPassenger($data);

            return $this->json(['status' => 'Created', 'id' => $passenger->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return $this->json(['error' => 'Passenger not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['first_name'])) $passenger->setFirstName($data['first_name']);
        if (isset($data['last_name'])) $passenger->setLastName($data['last_name']);
        if (isset($data['email'])) $passenger->setEmail($data['email']);
        if (isset($data['phone'])) $passenger->setPhone($data['phone']);
        if (isset($data['passport_number'])) $passenger->setPassportNumber($data['passport_number']);
        
        if (isset($data['date_of_birth'])) {
            try {
                $passenger->setDateOfBirth(new \DateTime($data['date_of_birth']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Invalid date format'], 400);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $passenger->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $passenger = $this->entityManager->getRepository(Passenger::class)->find($id);

        if (!$passenger) {
            return $this->json(['error' => 'Passenger not found'], 404);
        }

        try {
            $this->entityManager->remove($passenger);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete passenger because they have linked bookings/tickets'], 400);
        }

        return $this->json(['status' => 'Deleted']);
    }
}