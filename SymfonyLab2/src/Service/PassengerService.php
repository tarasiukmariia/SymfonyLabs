<?php

namespace App\Service;

use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PassengerService
{
    public function __construct(private EntityManagerInterface $entityManager) {}

    public function createPassenger(array $data): Passenger
    {
        $existing = $this->entityManager->getRepository(Passenger::class)->findOneBy(['email' => $data['email']]);
        if ($existing) {
            throw new ConflictHttpException('Passenger with this email already exists');
        }

        $passenger = new Passenger();
        $passenger->setFirstName($data['first_name']);
        $passenger->setLastName($data['last_name']);
        $passenger->setEmail($data['email']);
        $passenger->setPassportNumber($data['passport_number']);
        
        try {
            $passenger->setDateOfBirth(new \DateTime($data['date_of_birth']));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format for date_of_birth');
        }
        
        if (isset($data['phone'])) {
            $passenger->setPhone($data['phone']);
        }

        $this->entityManager->persist($passenger);
        $this->entityManager->flush();

        return $passenger;
    }
}