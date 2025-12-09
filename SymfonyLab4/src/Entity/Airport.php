<?php

namespace App\Entity;

use App\Repository\AirportRepository; 
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AirportRepository::class)]
#[ORM\Table(name: 'airports')]
class Airport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 150)]
    #[Assert\NotBlank(message: "Назва аеропорту не може бути пустою")]
    #[Assert\Length(
        min: 3,
        max: 150,
        minMessage: "Назва аеропорту має містити мінімум {{ limit }} символи",
        maxMessage: "Назва аеропорту не може перевищувати {{ limit }} символів"
    )]
    private ?string $name = null;

    #[ORM\Column(length: 3, unique: true)]
    #[Assert\NotBlank(message: "IATA код обов'язковий")]
    #[Assert\Length(
        min: 3,
        max: 3,
        exactMessage: "IATA код повинен складатися рівно з {{ limit }} символів"
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]{3}$/',
        message: "IATA код повинен складатися з 3 великих латинських літер (наприклад KBP)"
    )]
    private ?string $iataCode = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Місто не може бути пустим")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Назва міста не може перевищувати {{ limit }} символів"
    )]
    private ?string $city = null;

    #[ORM\ManyToOne(targetEntity: Country::class)]
    #[ORM\JoinColumn(nullable: false, name: 'country_id')]
    #[Assert\NotNull(message: "Необхідно вказати країну")]
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