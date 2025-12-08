<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'travel_classes')]
class TravelClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    private ?string $priceMultiplier = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
        return $this;
    }

    public function getPriceMultiplier(): ?string
    {
        return $this->priceMultiplier;
    }

    public function setPriceMultiplier(string $priceMultiplier): static
    {
        $this->priceMultiplier = $priceMultiplier;
        return $this;
    }
}