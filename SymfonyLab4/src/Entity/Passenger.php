<?php

namespace App\Entity;

use App\Repository\PassengerRepository; 
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PassengerRepository::class)] 
#[ORM\Table(name: 'passengers')]
class Passenger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Ім'я не може бути пустим")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Ім'я має містити мінімум {{ limit }} символи",
        maxMessage: "Ім'я не може перевищувати {{ limit }} символів"
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Прізвище не може бути пустим")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Прізвище має містити мінімум {{ limit }} символи",
        maxMessage: "Прізвище не може перевищувати {{ limit }} символів"
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 150, unique: true)]
    #[Assert\NotBlank(message: "Email обов'язковий")]
    #[Assert\Email(message: "Email '{{ value }}' не є валідним")]
    private ?string $email = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Assert\Length(
        max: 20,
        maxMessage: "Номер телефону не може перевищувати {{ limit }} символів"
    )]
    private ?string $phone = null;

    #[ORM\Column(length: 20, unique: true)]
    #[Assert\NotBlank(message: "Номер паспорта обов'язковий")]
    #[Assert\Length(
        min: 6,
        max: 20,
        minMessage: "Номер паспорта має містити мінімум {{ limit }} символів",
        maxMessage: "Номер паспорта не може перевищувати {{ limit }} символів"
    )]
    private ?string $passportNumber = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotNull(message: "Дата народження обов'язкова")]
    #[Assert\LessThan(value: "today", message: "Дата народження має бути в минулому")]
    private ?\DateTimeInterface $dateOfBirth = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;
        return $this;
    }

    public function getPassportNumber(): ?string
    {
        return $this->passportNumber;
    }

    public function setPassportNumber(string $passportNumber): static
    {
        $this->passportNumber = $passportNumber;
        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(\DateTimeInterface $dateOfBirth): static
    {
        $this->dateOfBirth = $dateOfBirth;
        return $this;
    }
}