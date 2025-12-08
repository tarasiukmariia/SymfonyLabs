<?php

namespace App\Service;

use App\Entity\Booking;
use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BookingService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createBooking(array $data): Booking
    {
        $booker = $this->entityManager->getRepository(Passenger::class)->find($data['booker_id']);
        
        if (!$booker) {
            throw new NotFoundHttpException('Passenger (booker) not found');
        }

        $booking = new Booking();
        $booking->setBookingReference($data['booking_reference']);
        $booking->setBooker($booker);
        $booking->setTotalAmount((string)$data['total_amount']);
        
        if (isset($data['status'])) {
            $booking->setStatus($data['status']);
        }
        
        if (isset($data['booking_date'])) {
            try {
                $booking->setBookingDate(new \DateTime($data['booking_date']));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid date format for booking_date');
            }
        }

        $this->entityManager->persist($booking);
        $this->entityManager->flush();

        return $booking;
    }
}