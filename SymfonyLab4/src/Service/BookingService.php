<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createBooking(
        int $bookerId,
        string $bookingReference,
        string $totalAmount,
        ?string $status,
        ?string $bookingDate
    ): Booking {
        $booker = $this->entityManager->getRepository(Passenger::class)->find($bookerId);
        
        if (!$booker) {
            throw new NotFoundHttpException('Passenger (booker) not found with id ' . $bookerId);
        }

        $booking = $this->createBookingObject($booker, $bookingReference, $totalAmount, $status, $bookingDate);

        $this->requestCheckerService->validateRequestDataByConstraints($booking);

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }

    private function createBookingObject(
        Passenger $booker,
        string $bookingReference,
        string $totalAmount,
        ?string $status,
        ?string $bookingDate
    ): Booking {
        $booking = new Booking();
        $booking->setBooker($booker);
        $booking->setBookingReference($bookingReference);
        $booking->setTotalAmount($totalAmount);

        if ($status) {
            $booking->setStatus($status);
        }

        if ($bookingDate) {
            try {
                $booking->setBookingDate(new \DateTime($bookingDate));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format for booking_date');
            }
        }

        return $booking;
    }

    public function updateBooking(Booking $booking, array $data): void
    {
        if (array_key_exists('booker_id', $data)) {
            $booker = $this->entityManager->getRepository(Passenger::class)->find($data['booker_id']);
            if (!$booker) {
                throw new NotFoundHttpException('Passenger (booker) not found');
            }
            $booking->setBooker($booker);
            unset($data['booker_id']);
        }

        if (array_key_exists('booking_date', $data)) {
            if ($data['booking_date']) {
                try {
                    $booking->setBookingDate(new \DateTime($data['booking_date']));
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException("Invalid date format");
                }
            }
            unset($data['booking_date']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($booking, $method)) {
                $booking->$method((string)$value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($booking);
        
        $this->entityManager->flush();
    }
}