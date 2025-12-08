<?php

namespace App\Service;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class RequestValidatorService
{
    public function validateRequiredFields(array $data, array $requiredFields): void
    {
        $missingFields = [];

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null || $data[$field] === '') {
                $missingFields[] = $field;
            }
        }

        if (!empty($missingFields)) {
            throw new BadRequestHttpException(
                sprintf('Missing required fields: %s', implode(', ', $missingFields))
            );
        }
    }
}