<?php

namespace App\Controller;

use App\Entity\Country;
use App\Service\CountryService;
use App\Service\RequestValidatorService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/countries')]
class CountryController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private CountryService $countryService,         
        private RequestValidatorService $validator      
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $countries = $this->entityManager->getRepository(Country::class)->findAll();
        
        $data = [];
        foreach ($countries as $country) {
            $data[] = [
                'id' => $country->getId(),
                'name' => $country->getName(),
                'code' => $country->getCode(),
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], 404);
        }

        return $this->json([
            'id' => $country->getId(),
            'name' => $country->getName(),
            'code' => $country->getCode(),
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $requiredFields = ['name', 'code'];
            $this->validator->validateRequiredFields($data, $requiredFields);

            $country = $this->countryService->createCountry($data);

            return $this->json(['status' => 'Created', 'id' => $country->getId()], 201);

        } catch (HttpException $e) {
            return $this->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], 400);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $country->setName($data['name']);
        }
        if (isset($data['code'])) {
            $country->setCode($data['code']);
        }

        $this->entityManager->flush();

        return $this->json([
            'status' => 'Updated',
            'id' => $country->getId(),
            'name' => $country->getName(),
            'code' => $country->getCode()
        ]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return $this->json(['error' => 'Country not found'], 404);
        }
        
        try {
            $this->entityManager->remove($country);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete country because it is linked to other data (airports)'], 400);
        }

        return $this->json(['status' => 'Deleted']);
    }
}