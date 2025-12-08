<?php

namespace App\Service;

use App\Entity\Baggage;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaggageService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createBaggage(array $data): Baggage
    {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($data['ticket_id']);
        
        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found');
        }

        $baggage = new Baggage();
        $baggage->setTicket($ticket);
        $baggage->setWeightKg((string)$data['weight_kg']);
        $baggage->setType($data['type'] ?? 'checked'); 
        $baggage->setPrice((string)($data['price'] ?? 0));

        $this->entityManager->persist($baggage);
        $this->entityManager->flush();

        return $baggage;
    }
}