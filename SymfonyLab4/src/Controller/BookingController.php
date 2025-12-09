<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Repository\BookingRepository; 
use App\Service\BookingService;
use App\Service\RequestCheckerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    private const REQUIRED_FIELDS = ['booker_id', 'booking_reference', 'total_amount'];
    private const ITEMS_PER_PAGE = 10;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private BookingService $bookingService,
        private RequestCheckerService $requestCheckerService
    ) {}

    #[Route('', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        $requestData = $request->query->all();
        $page = (int) ($requestData['page'] ?? 1);
        $itemsPerPage = (int) ($requestData['itemsPerPage'] ?? self::ITEMS_PER_PAGE);

        $repository = $this->entityManager->getRepository(Booking::class);

        $result = $repository->getAllBookingsByFilter($requestData, $itemsPerPage, $page);
        
        return $this->json($result);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($booking);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        try {
            $this->requestCheckerService->check($data, self::REQUIRED_FIELDS);

            $booking = $this->bookingService->createBooking(
                (int)$data['booker_id'],
                $data['booking_reference'],
                (string)$data['total_amount'],
                $data['status'] ?? null,
                $data['booking_date'] ?? null
            );

            $this->entityManager->flush();

            return $this->json($booking, Response::HTTP_CREATED);

        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        try {
            $this->bookingService->updateBooking($booking, $data);
            
            $this->entityManager->flush();

            return $this->json($booking);
        } catch (HttpException $e) {
            throw $e;
        } catch (\Exception $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], Response::HTTP_NOT_FOUND);
        }

        try {
            $this->entityManager->remove($booking);
            $this->entityManager->flush();
        } catch (\Exception $e) {
            return $this->json(['error' => 'Cannot delete booking because it has linked tickets'], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['status' => 'Deleted']);
    }
}