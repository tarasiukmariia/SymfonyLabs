<?php

namespace App\Entity;

use App\Repository\TravelClassRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TravelClassRepository::class)] 
#[ORM\Table(name: 'travel_classes')]
class TravelClass
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(message: "Назва класу не може бути пустою")]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: "Назва класу має містити мінімум {{ limit }} символи",
        maxMessage: "Назва класу не може перевищувати {{ limit }} символів"
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 2)]
    #[Assert\NotBlank(message: "Коефіцієнт ціни обов'язковий")]
    #[Assert\Positive(message: "Коефіцієнт має бути більшим за 0")]
    #[Assert\Range(
        min: 0.1,
        max: 9.99,
        notInRangeMessage: "Коефіцієнт має бути в межах від {{ min }} до {{ max }}"
    )]
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