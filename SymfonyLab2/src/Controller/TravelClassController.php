<?php

namespace App\Controller;

use App\Entity\TravelClass;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/travel-classes')]
class TravelClassController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $classes = $this->entityManager->getRepository(TravelClass::class)->findAll();
        
        $data = [];
        foreach ($classes as $class) {
            $data[] = [
                'id' => $class->getId(),
                'name' => $class->getName(),
                'price_multiplier' => $class->getPriceMultiplier(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], 404);
        }

        return $this->json([
            'id' => $class->getId(),
            'name' => $class->getName(),
            'price_multiplier' => $class->getPriceMultiplier(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['price_multiplier'])) {
            return $this->json(['error' => 'Missing required fields (name, price_multiplier)'], 400);
        }

        $class = new TravelClass();
        $class->setName($data['name']);
        $class->setPriceMultiplier((string)$data['price_multiplier']);

        $this->entityManager->persist($class);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $class->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $class->setName($data['name']);
        }
        if (isset($data['price_multiplier'])) {
            $class->setPriceMultiplier((string)$data['price_multiplier']);
        }

        $this->entityManager->flush();

        return $this->json([
            'status' => 'Updated',
            'id' => $class->getId(),
            'name' => $class->getName(),
            'price_multiplier' => $class->getPriceMultiplier()
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $class = $this->entityManager->getRepository(TravelClass::class)->find($id);

        if (!$class) {
            return $this->json(['error' => 'Travel Class not found'], 404);
        }

        try {
            $this->entityManager->remove($class);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete this class because it is used in tickets'], 400);
        }

        return $this->json(['status' => 'Deleted']);
    }
}