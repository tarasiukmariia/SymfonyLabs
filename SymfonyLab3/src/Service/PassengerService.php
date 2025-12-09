<?php

namespace App\Service;

use App\Entity\Passenger;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class PassengerService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private RequestCheckerService $requestCheckerService
    ) {}

    public function createPassenger(
        string $firstName,
        string $lastName,
        string $email,
        string $passportNumber,
        string $dateOfBirth,
        ?string $phone
    ): Passenger {
        $existing = $this->entityManager->getRepository(Passenger::class)->findOneBy(['email' => $email]);
        if ($existing) {
            throw new ConflictHttpException('Passenger with this email already exists');
        }

        $passenger = $this->createPassengerObject(
            $firstName,
            $lastName,
            $email,
            $passportNumber,
            $dateOfBirth,
            $phone
        );

        $this->requestCheckerService->validateRequestDataByConstraints($passenger);

        $this->entityManager->persist($passenger);
        $this->entityManager->flush();

        return $passenger;
    }

    private function createPassengerObject(
        string $firstName,
        string $lastName,
        string $email,
        string $passportNumber,
        string $dateOfBirth,
        ?string $phone
    ): Passenger {
        $passenger = new Passenger();
        $passenger->setFirstName($firstName);
        $passenger->setLastName($lastName);
        $passenger->setEmail($email);
        $passenger->setPassportNumber($passportNumber);
        
        if ($phone) {
            $passenger->setPhone($phone);
        }

        try {
            $passenger->setDateOfBirth(new \DateTime($dateOfBirth));
        } catch (\Exception $e) {
            throw new \InvalidArgumentException('Invalid date format for date_of_birth');
        }

        return $passenger;
    }

    public function updatePassenger(Passenger $passenger, array $data): void
    {
        if (array_key_exists('date_of_birth', $data)) {
            if ($data['date_of_birth']) {
                try {
                    $passenger->setDateOfBirth(new \DateTime($data['date_of_birth']));
                } catch (\Exception $e) {
                    throw new \InvalidArgumentException("Invalid date format");
                }
            }
            unset($data['date_of_birth']);
        }

        foreach ($data as $key => $value) {
            $method = 'set' . str_replace('_', '', ucwords($key, '_'));
            
            if (method_exists($passenger, $method)) {
                $passenger->$method($value);
            }
        }

        $this->requestCheckerService->validateRequestDataByConstraints($passenger);
        
        $this->entityManager->flush();
    }
}