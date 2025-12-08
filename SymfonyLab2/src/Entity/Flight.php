<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'flights')]
class Flight
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    private ?string $flightNumber = null;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[ORM\JoinColumn(nullable: false, name: 'departure_airport_id')]
    private ?Airport $departureAirport = null;

    #[ORM\ManyToOne(targetEntity: Airport::class)]
    #[ORM\JoinColumn(nullable: false, name: 'arrival_airport_id')]
    private ?Airport $arrivalAirport = null;

    #[ORM\ManyToOne(targetEntity: Aircraft::class)]
    #[ORM\JoinColumn(nullable: true, name: 'aircraft_id')]
    private ?Aircraft $aircraft = null;

    #[ORM\Column(length: 20, options: ['default' => 'Scheduled'])]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $departureTime = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $arrivalTime = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $basePrice = null;

    public function __construct()
    {
        $this->status = 'Scheduled';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFlightNumber(): ?string
    {
        return $this->flightNumber;
    }

    public function setFlightNumber(string $flightNumber): static
    {
        $this->flightNumber = $flightNumber;
        return $this;
    }

    public function getDepartureAirport(): ?Airport
    {
        return $this->departureAirport;
    }

    public function setDepartureAirport(?Airport $departureAirport): static
    {
        $this->departureAirport = $departureAirport;
        return $this;
    }

    public function getArrivalAirport(): ?Airport
    {
        return $this->arrivalAirport;
    }

    public function setArrivalAirport(?Airport $arrivalAirport): static
    {
        $this->arrivalAirport = $arrivalAirport;
        return $this;
    }

    public function getAircraft(): ?Aircraft
    {
        return $this->aircraft;
    }

    public function setAircraft(?Aircraft $aircraft): static
    {
        $this->aircraft = $aircraft;
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

    public function getDepartureTime(): ?\DateTimeInterface
    {
        return $this->departureTime;
    }

    public function setDepartureTime(\DateTimeInterface $departureTime): static
    {
        $this->departureTime = $departureTime;
        return $this;
    }

    public function getArrivalTime(): ?\DateTimeInterface
    {
        return $this->arrivalTime;
    }

    public function setArrivalTime(\DateTimeInterface $arrivalTime): static
    {
        $this->arrivalTime = $arrivalTime;
        return $this;
    }

    public function getBasePrice(): ?string
    {
        return $this->basePrice;
    }

    public function setBasePrice(string $basePrice): static
    {
        $this->basePrice = $basePrice;
        return $this;
    }
}