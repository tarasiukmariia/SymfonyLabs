<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'aircrafts')]
class Aircraft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private ?string $registrationNumber = null;

    #[ORM\ManyToOne(targetEntity: AircraftModel::class)]
    #[ORM\JoinColumn(nullable: false, name: 'model_id')] 
    private ?AircraftModel $model = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $manufactureDate = null;

    #[ORM\Column]
    private ?int $totalCapacity = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->registrationNumber;
    }

    public function setRegistrationNumber(string $registrationNumber): static
    {
        $this->registrationNumber = $registrationNumber;
        return $this;
    }

    public function getModel(): ?AircraftModel
    {
        return $this->model;
    }

    public function setModel(?AircraftModel $model): static
    {
        $this->model = $model;
        return $this;
    }

    public function getManufactureDate(): ?\DateTimeInterface
    {
        return $this->manufactureDate;
    }

    public function setManufactureDate(?\DateTimeInterface $manufactureDate): static
    {
        $this->manufactureDate = $manufactureDate;
        return $this;
    }

    public function getTotalCapacity(): ?int
    {
        return $this->totalCapacity;
    }

    public function setTotalCapacity(int $totalCapacity): static
    {
        $this->totalCapacity = $totalCapacity;
        return $this;
    }
}