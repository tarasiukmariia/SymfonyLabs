<?php

namespace App\Controller;

use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/passengers')]
class PassengerController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

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

        $requiredFields = ['first_name', 'last_name', 'email', 'passport_number', 'date_of_birth'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Missing required field: $field"], 400);
            }
        }

        $existing = $this->entityManager->getRepository(Passenger::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            return $this->json(['error' => 'Passenger with this email already exists'], 409); 
        }

        $passenger = new Passenger();
        $passenger->setFirstName($data['first_name']);
        $passenger->setLastName($data['last_name']);
        $passenger->setEmail($data['email']);
        $passenger->setPassportNumber($data['passport_number']);
        $passenger->setDateOfBirth(new \DateTime($data['date_of_birth']));
        
        if (isset($data['phone'])) {
            $passenger->setPhone($data['phone']);
        }

        $this->entityManager->persist($passenger);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $passenger->getId()], 201);
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
        if (isset($data['date_of_birth'])) $passenger->setDateOfBirth(new \DateTime($data['date_of_birth']));

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