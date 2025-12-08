<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/test')]
final class TestController extends AbstractController
{
    private array $items = [
        1 => ['id' => 1, 'name' => 'Тестовий запис 1', 'status' => 'active'],
        2 => ['id' => 2, 'name' => 'Тестовий запис 2', 'status' => 'pending'],
        3 => ['id' => 3, 'name' => 'Тестовий запис 3', 'status' => 'done'],
    ];

    #[Route('', name: 'app_test_index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json($this->items);
    }

    #[Route('/{id}', name: 'app_test_show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Запис не знайдено'], 404);
        }

        return $this->json($this->items[$id]);
    }

    #[Route('', name: 'app_test_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $newId = count($this->items) + 1;
        
        $newItem = [
            'id' => $newId,
            'name' => $data['name'] ?? 'Новий запис',
            'status' => $data['status'] ?? 'new'
        ];

        return $this->json($newItem, 201);
    }

    #[Route('/{id}', name: 'app_test_update', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Запис не знайдено'], 404);
        }

        $data = json_decode($request->getContent(), true);

        $updatedItem = array_merge($this->items[$id], $data);
        
        $this->items[$id] = $updatedItem;

        return $this->json($updatedItem);
    }

    #[Route('/{id}', name: 'app_test_delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        if (!isset($this->items[$id])) {
            return $this->json(['error' => 'Запис не знайдено'], 404);
        }
        return $this->json(['message' => 'Запис успішно видалено']);
    }
}