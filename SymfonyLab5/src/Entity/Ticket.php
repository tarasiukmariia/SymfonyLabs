<?php

namespace App\Entity;

use App\Repository\TicketRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TicketRepository::class)]
#[ORM\Table(name: 'tickets')]
class Ticket
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Booking::class)]
    #[ORM\JoinColumn(nullable: false, name: 'booking_id', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Квиток повинен бути прив'язаний до бронювання")]
    private ?Booking $booking = null;

    #[ORM\ManyToOne(targetEntity: Flight::class)]
    #[ORM\JoinColumn(nullable: false, name: 'flight_id')]
    #[Assert\NotNull(message: "Необхідно вказати рейс")]
    private ?Flight $flight = null;

    #[ORM\ManyToOne(targetEntity: Passenger::class)]
    #[ORM\JoinColumn(nullable: false, name: 'passenger_id')]
    #[Assert\NotNull(message: "Необхідно вказати пасажира")]
    private ?Passenger $passenger = null;

    #[ORM\ManyToOne(targetEntity: TravelClass::class)]
    #[ORM\JoinColumn(nullable: false, name: 'travel_class_id')]
    #[Assert\NotNull(message: "Необхідно вказати клас обслуговування")]
    private ?TravelClass $travelClass = null;

    #[ORM\Column(length: 5, nullable: true)]
    #[Assert\Length(
        max: 5,
        maxMessage: "Номер місця не може перевищувати {{ limit }} символів"
    )]
    private ?string $seatNumber = null; 

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Ціна квитка обов'язкова")]
    #[Assert\PositiveOrZero(message: "Ціна не може бути від'ємною")]
    private ?string $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBooking(): ?Booking
    {
        return $this->booking;
    }

    public function setBooking(?Booking $booking): static
    {
        $this->booking = $booking;
        return $this;
    }

    public function getFlight(): ?Flight
    {
        return $this->flight;
    }

    public function setFlight(?Flight $flight): static
    {
        $this->flight = $flight;
        return $this;
    }

    public function getPassenger(): ?Passenger
    {
        return $this->passenger;
    }

    public function setPassenger(?Passenger $passenger): static
    {
        $this->passenger = $passenger;
        return $this;
    }

    public function getTravelClass(): ?TravelClass
    {
        return $this->travelClass;
    }

    public function setTravelClass(?TravelClass $travelClass): static
    {
        $this->travelClass = $travelClass;
        return $this;
    }

    public function getSeatNumber(): ?string
    {
        return $this->seatNumber;
    }

    public function setSeatNumber(?string $seatNumber): static
    {
        $this->seatNumber = $seatNumber;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }
}