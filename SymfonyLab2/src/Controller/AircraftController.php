<?php

namespace App\Controller;

use App\Entity\Aircraft;
use App\Entity\AircraftModel;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/aircrafts')]
class AircraftController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $aircrafts = $this->entityManager->getRepository(Aircraft::class)->findAll();
        
        $data = [];
        foreach ($aircrafts as $aircraft) {
            $data[] = [
                'id' => $aircraft->getId(),
                'registration_number' => $aircraft->getRegistrationNumber(),
                'manufacture_date' => $aircraft->getManufactureDate()?->format('Y-m-d'),
                'total_capacity' => $aircraft->getTotalCapacity(),
                'model' => [
                    'id' => $aircraft->getModel()->getId(),
                    'name' => $aircraft->getModel()->getModelName(),
                    'manufacturer' => $aircraft->getModel()->getManufacturer(),
                ]
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], 404);
        }

        return $this->json([
            'id' => $aircraft->getId(),
            'registration_number' => $aircraft->getRegistrationNumber(),
            'manufacture_date' => $aircraft->getManufactureDate()?->format('Y-m-d'),
            'total_capacity' => $aircraft->getTotalCapacity(),
            'model' => [
                'id' => $aircraft->getModel()->getId(),
                'name' => $aircraft->getModel()->getModelName(),
            ]
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['model_id'])) {
            return $this->json(['error' => 'model_id is required'], 400);
        }

        $model = $this->entityManager->getRepository(AircraftModel::class)->find($data['model_id']);
        if (!$model) {
            return $this->json(['error' => 'Model not found'], 404);
        }

        $aircraft = new Aircraft();
        $aircraft->setRegistrationNumber($data['registration_number']);
        $aircraft->setTotalCapacity((int)$data['total_capacity']);
        
        if (isset($data['manufacture_date'])) {
            $aircraft->setManufactureDate(new \DateTime($data['manufacture_date']));
        }

        $aircraft->setModel($model);

        $this->entityManager->persist($aircraft);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $aircraft->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['registration_number'])) {
            $aircraft->setRegistrationNumber($data['registration_number']);
        }
        if (isset($data['total_capacity'])) {
            $aircraft->setTotalCapacity((int)$data['total_capacity']);
        }
        if (isset($data['manufacture_date'])) {
            $aircraft->setManufactureDate(new \DateTime($data['manufacture_date']));
        }
        
        if (isset($data['model_id'])) {
            $model = $this->entityManager->getRepository(AircraftModel::class)->find($data['model_id']);
            if ($model) {
                $aircraft->setModel($model);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $aircraft->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $aircraft = $this->entityManager->getRepository(Aircraft::class)->find($id);

        if (!$aircraft) {
            return $this->json(['error' => 'Aircraft not found'], 404);
        }

        $this->entityManager->remove($aircraft);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}