<?php

namespace App\Entity;

use App\Repository\CountryRepository; 
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CountryRepository::class)]
#[ORM\Table(name: 'countries')]
class Country
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(message: "Назва країни не може бути пустою")]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: "Назва країни має містити мінімум {{ limit }} символи",
        maxMessage: "Назва країни не може перевищувати {{ limit }} символів"
    )]
    private ?string $name = null;

    #[ORM\Column(length: 3, unique: true)]
    #[Assert\NotBlank(message: "Код країни обов'язковий")]
    #[Assert\Length(
        min: 2,
        max: 3,
        minMessage: "Код країни має містити мінімум {{ limit }} символи",
        maxMessage: "Код країни не може перевищувати {{ limit }} символи"
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z]{2,3}$/',
        message: "Код країни має складатися з 2 або 3 великих латинських літер (наприклад UA або USA)"
    )]
    private ?string $code = null;

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

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;
        return $this;
    }
}