<?php

namespace App\Entity;

use App\Repository\BookingRepository; 
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BookingRepository::class)]
#[ORM\Table(name: 'bookings')]
class Booking
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10, unique: true)]
    #[Assert\NotBlank(message: "Код бронювання обов'язковий")]
    #[Assert\Length(
        min: 6,
        max: 10,
        minMessage: "Код бронювання має містити мінімум {{ limit }} символів",
        maxMessage: "Код бронювання не може перевищувати {{ limit }} символів"
    )]
    private ?string $bookingReference = null;

    #[ORM\ManyToOne(targetEntity: Passenger::class)]
    #[ORM\JoinColumn(nullable: false, name: 'booker_id')]
    #[Assert\NotNull(message: "Необхідно вказати пасажира (хто бронює)")]
    private ?Passenger $booker = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, options: ['default' => 'CURRENT_TIMESTAMP'])]
    #[Assert\NotNull(message: "Дата бронювання обов'язкова")]
    private ?\DateTimeInterface $bookingDate = null;

    #[ORM\Column(length: 20, options: ['default' => 'Pending'])]
    #[Assert\Choice(
        choices: ['Pending', 'Confirmed', 'Cancelled'],
        message: "Статус має бути 'Pending', 'Confirmed' або 'Cancelled'"
    )]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    #[Assert\NotBlank(message: "Сума бронювання обов'язкова")]
    #[Assert\PositiveOrZero(message: "Сума не може бути від'ємною")]
    private ?string $totalAmount = null;

    public function __construct()
    {
        $this->bookingDate = new \DateTime();
        $this->status = 'Pending';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBookingReference(): ?string
    {
        return $this->bookingReference;
    }

    public function setBookingReference(string $bookingReference): static
    {
        $this->bookingReference = $bookingReference;
        return $this;
    }

    public function getBooker(): ?Passenger
    {
        return $this->booker;
    }

    public function setBooker(?Passenger $booker): static
    {
        $this->booker = $booker;
        return $this;
    }

    public function getBookingDate(): ?\DateTimeInterface
    {
        return $this->bookingDate;
    }

    public function setBookingDate(\DateTimeInterface $bookingDate): static
    {
        $this->bookingDate = $bookingDate;
        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getTotalAmount(): ?string
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(string $totalAmount): static
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }
}