<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'aircraft_models')]
class AircraftModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 100)] 
    private ?string $modelName = null;

    #[ORM\Column]
    private ?int $maxRangeKm = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getManufacturer(): ?string
    {
        return $this->manufacturer;
    }

    public function setManufacturer(string $manufacturer): static
    {
        $this->manufacturer = $manufacturer;
        return $this;
    }

    public function getModelName(): ?string
    {
        return $this->modelName;
    }

    public function setModelName(string $modelName): static
    {
        $this->modelName = $modelName;
        return $this;
    }

    public function getMaxRangeKm(): ?int
    {
        return $this->maxRangeKm;
    }

    public function setMaxRangeKm(int $maxRangeKm): static
    {
        $this->maxRangeKm = $maxRangeKm;
        return $this;
    }
}