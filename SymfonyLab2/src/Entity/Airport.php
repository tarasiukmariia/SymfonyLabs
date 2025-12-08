<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'airports')]
class Airport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    private ?string $name = null;

    #[ORM\Column(length: 3, unique: true)]
    private ?string $iataCode = null;

    #[ORM\Column(length: 100)]
    private ?string $city = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: false, name: 'country_id')]
    private ?Country $country = null;

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

    public function getIataCode(): ?string
    {
        return $this->iataCode;
    }

    public function setIataCode(string $iataCode): static
    {
        $this->iataCode = $iataCode;
        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;
        return $this;
    }

    public function getCountry(): ?Country
    {
        return $this->country;
    }

    public function setCountry(?Country $country): static
    {
        $this->country = $country;
        return $this;
    }
}