<?php

namespace App\Entity;

use App\Repository\AircraftModelRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AircraftModelRepository::class)]
#[ORM\Table(name: 'aircraft_models')]
class AircraftModel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Виробник не може бути пустим")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Назва виробника має містити мінімум {{ limit }} символи",
        maxMessage: "Назва виробника не може перевищувати {{ limit }} символів"
    )]
    private ?string $manufacturer = null;

    #[ORM\Column(length: 100)] 
    #[Assert\NotBlank(message: "Назва моделі не може бути пустою")]
    #[Assert\Length(
        max: 100,
        maxMessage: "Назва моделі не може перевищувати {{ limit }} символів"
    )]
    private ?string $modelName = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Максимальна дальність польоту обов'язкова")]
    #[Assert\Positive(message: "Дальність польоту має бути більше нуля")]
    #[Assert\Range(
        min: 100,
        max: 20000,
        notInRangeMessage: "Дальність польоту має бути в межах від {{ min }} до {{ max }} км"
    )]
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