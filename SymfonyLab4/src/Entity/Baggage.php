<?php

namespace App\Entity;

use App\Repository\BaggageRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: BaggageRepository::class)]
#[ORM\Table(name: 'baggage')]
class Baggage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Ticket::class)]
    #[ORM\JoinColumn(nullable: false, name: 'ticket_id', onDelete: 'CASCADE')]
    #[Assert\NotNull(message: "Багаж повинен бути прив'язаний до квитка")]
    private ?Ticket $ticket = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 5, scale: 2)]
    #[Assert\NotBlank(message: "Вага багажу обов'язкова")]
    #[Assert\Positive(message: "Вага має бути більшою за 0")]
    #[Assert\LessThan(
        value: 100, 
        message: "Вага одного місця багажу не може перевищувати 100 кг"
    )]
    private ?string $weightKg = null;

    #[ORM\Column(length: 20)] 
    #[Assert\NotBlank(message: "Тип багажу обов'язковий")]
    #[Assert\Choice(
        choices: ['carry_on', 'checked'], 
        message: "Тип багажу має бути 'carry_on' (ручна поклажа) або 'checked' (зареєстрований багаж)"
    )]
    private ?string $type = null; 

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    #[Assert\PositiveOrZero(message: "Ціна не може бути від'ємною")]
    private ?string $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTicket(): ?Ticket
    {
        return $this->ticket;
    }

    public function setTicket(?Ticket $ticket): static
    {
        $this->ticket = $ticket;
        return $this;
    }

    public function getWeightKg(): ?string
    {
        return $this->weightKg;
    }

    public function setWeightKg(string $weightKg): static
    {
        $this->weightKg = $weightKg;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(?string $price): static
    {
        $this->price = $price;
        return $this;
    }
}