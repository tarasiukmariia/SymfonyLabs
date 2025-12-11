<?php

namespace App\Controller;

use App\Entity\Country;
use App\Repository\CountryRepository;
use App\Service\CountryService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/countries')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class CountryController extends AbstractController
{
    private const REQUIRED_FIELDS = ['name', 'code'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private CountryService $countryService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', name: 'app_countries_collection', methods: [Request::METHOD_GET])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Country::class);

        $result = $repository->getAllCountriesByFilter($requestData, $itemsPerPage, $page);
        
        return new JsonResponse($result, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'app_countries_item', methods: [Request::METHOD_GET])]
    public function show(int $id): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return new JsonResponse(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse($country, Response::HTTP_OK);
    }

    #[Route('', name: 'app_countries_create', methods: [Request::METHOD_POST])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->requestCheckerService->check($data, fields: self::REQUIRED_FIELDS);

            $country = $this->countryService->createCountry(
                $data['name'],
                $data['code']
            );

            $this->entityManager->flush();

            return new JsonResponse($country, status: Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_countries_update', methods: [Request::METHOD_PUT, Request::METHOD_PATCH])]
    public function update(int $id, Request $request): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return new JsonResponse(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), associative: true);

        try {
            $this->countryService->updateCountry($country, $data);
            
            $this->entityManager->flush();

            return new JsonResponse($country, Response::HTTP_OK);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', name: 'app_countries_delete', methods: [Request::METHOD_DELETE])]
    public function delete(int $id): JsonResponse
    {
        $country = $this->entityManager->getRepository(Country::class)->find($id);

        if (!$country) {
            return new JsonResponse(['error' => 'Country not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $this->entityManager->remove($country);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Cannot delete country because it is linked to other data (airports)'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['status' => 'Deleted'], Response::HTTP_OK);
    }
}