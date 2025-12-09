<?php

namespace App\Entity;

use App\Repository\AircraftRepository; 
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: AircraftRepository::class)] 
#[ORM\Table(name: 'aircrafts')]
class Aircraft
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "Бортовий номер не може бути пустим")]
    #[Assert\Length(
        min: 3,
        max: 20,
        minMessage: "Бортовий номер має містити мінімум {{ limit }} символи",
        maxMessage: "Бортовий номер не може перевищувати {{ limit }} символів"
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z0-9-]+$/', 
        message: "Бортовий номер може містити лише великі латинські літери, цифри та дефіс"
    )]
    private ?string $registrationNumber = null;

    #[ORM\ManyToOne(targetEntity: AircraftModel::class)]
    #[ORM\JoinColumn(nullable: false, name: 'model_id')]
    #[Assert\NotNull(message: "Необхідно вказати модель літака")]
    private ?AircraftModel $model = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Assert\LessThanOrEqual(
        value: "today", 
        message: "Дата виробництва не може бути в майбутньому"
    )]
    private ?\DateTimeInterface $manufactureDate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Кількість місць обов'язкова")]
    #[Assert\Positive(message: "Кількість місць має бути додатною")]
    #[Assert\Range(
        min: 1, 
        max: 1000, 
        notInRangeMessage: "Кількість місць повинна бути від {{ min }} до {{ max }}"
    )]
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