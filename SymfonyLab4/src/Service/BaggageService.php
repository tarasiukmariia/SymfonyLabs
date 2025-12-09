<?php

namespace App\Service;

use App\Entity\Baggage;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BaggageService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createBaggage(
        int $ticketId,
        string $weightKg,
        string $type,
        ?string $price
    ): Baggage {
        $ticket = $this->entityManager->getRepository(Ticket::class)->find($ticketId);
        
        if (!$ticket) {
            throw new NotFoundHttpException('Ticket not found with id ' . $ticketId);
        }

        $baggage = $this->createBaggageObject($ticket, $weightKg, $type, $price);

        $this->requestCheckerService->validateRequestDataByConstraints($baggage);

        $this->entityManager->persist($baggage);
        $this->entityManager->flush();

        return $baggage;
    }

    private function createBaggageObject(
        Ticket $ticket,
        string $weightKg,
        string $type,
        ?string $price
    ): Baggage {
        $baggage = new Baggage();
        $baggage->setTicket($ticket);
        $baggage->setWeightKg($weightKg);
        $baggage->setType($type);
        $baggage->setPrice($price ?? '0.00');

        return $baggage;
    }

    public function updateBaggage(Baggage $baggage, array $data): void
    {
        if (array_key_exists('ticket_id', $data)) {
            $ticket = $this->entityManager->getRepository(Ticket::class)->find($data['ticket_id']);
            if (!$ticket) {
                throw new NotFoundHttpException('Ticket not found');
            }
            $baggage->setTicket($ticket);
            unset($data['ticket_id']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($baggage, $method)) {
                $baggage->$method((string)$value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($baggage);
        
        $this->entityManager->flush();
    }
}