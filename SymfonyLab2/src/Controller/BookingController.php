<?php

namespace App\Controller;

use App\Entity\Booking;
use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/bookings')]
class BookingController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $bookings = $this->entityManager->getRepository(Booking::class)->findAll();
        
        $data = [];
        foreach ($bookings as $booking) {
            $data[] = [
                'id' => $booking->getId(),
                'booking_reference' => $booking->getBookingReference(),
                'status' => $booking->getStatus(),
                'total_amount' => $booking->getTotalAmount(),
                'booking_date' => $booking->getBookingDate()->format('Y-m-d H:i:s'),
                'booker_id' => $booking->getBooker()->getId() 
            ];
        }

        return $this->json($data);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        return $this->json([
            'id' => $booking->getId(),
            'booking_reference' => $booking->getBookingReference(),
            'status' => $booking->getStatus(),
            'total_amount' => $booking->getTotalAmount(),
            'booking_date' => $booking->getBookingDate()->format('Y-m-d H:i:s'),
            'booker_id' => $booking->getBooker()->getId()
        ]);
    }

    #[Route('', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['booker_id']) || !isset($data['booking_reference']) || !isset($data['total_amount'])) {
            return $this->json(['error' => 'Missing required fields (booker_id, booking_reference, total_amount)'], 400);
        }

        $booker = $this->entityManager->getRepository(Passenger::class)->find($data['booker_id']);
        if (!$booker) {
            return $this->json(['error' => 'Passenger (booker) not found'], 404);
        }

        $booking = new Booking();
        $booking->setBookingReference($data['booking_reference']);
        $booking->setBooker($booker);
        $booking->setTotalAmount((string)$data['total_amount']);
        
        if (isset($data['status'])) {
            $booking->setStatus($data['status']);
        }
        if (isset($data['booking_date'])) {
            $booking->setBookingDate(new \DateTime($data['booking_date']));
        }

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $this->json(['status' => 'Created', 'id' => $booking->getId()], 201);
    }

    #[Route('/{id}', methods: ['PUT', 'PATCH'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['booking_reference'])) {
            $booking->setBookingReference($data['booking_reference']);
        }
        if (isset($data['status'])) {
            $booking->setStatus($data['status']);
        }
        if (isset($data['total_amount'])) {
            $booking->setTotalAmount((string)$data['total_amount']);
        }
        
        if (isset($data['booker_id'])) {
            $booker = $this->entityManager->getRepository(Passenger::class)->find($data['booker_id']);
            if ($booker) {
                $booking->setBooker($booker);
            }
        }

        $this->entityManager->flush();

        return $this->json(['status' => 'Updated', 'id' => $booking->getId()]);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $booking = $this->entityManager->getRepository(Booking::class)->find($id);

        if (!$booking) {
            return $this->json(['error' => 'Booking not found'], 404);
        }

        $this->entityManager->remove($booking);
        $this->entityManager->flush();

        return $this->json(['status' => 'Deleted']);
    }
}