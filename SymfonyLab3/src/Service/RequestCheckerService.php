<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class RequestCheckerService
{
    public function __construct(private ValidatorInterface $validator)
    {
    }

    public function check(mixed $content, array $fields): bool
    {
        $errors = '';

        if (empty($content)) {
            throw new BadRequestHttpException('Empty content', null, Response::HTTP_BAD_REQUEST);
        }

        if (!is_array($content)) {
            throw new BadRequestHttpException('Content must be an array/json object', null, Response::HTTP_BAD_REQUEST);
        }

        foreach ($fields as $field) {
            if (!array_key_exists($field, $content)) {
                $errors .= ' ' . $field . ';';
            }
        }

        if ($errors) {
            throw new BadRequestHttpException('Required fields are missed:' . $errors, null, Response::HTTP_BAD_REQUEST);
        }

        return true;
    }

    public function validateRequestDataByConstraints(array|object $data, ?array $constraints = null, ?bool $removeSquareBracketFromPropertyPath = false): void
    {
        $constraint = !empty($constraints) ? new Collection($constraints) : null;

        $violations = $this->validator->validate($data, $constraint);

        if (count($violations) === 0) {
            return;
        }

        $validationErrors = [];

        foreach ($violations as $violation) {
            $key = str_replace(['[', ']'], ['', ''], $violation->getPropertyPath());

            if ($removeSquareBracketFromPropertyPath) {
                $key = preg_replace('/\[.*?\]/', '', $violation->getPropertyPath());
            }

            $validationErrors[$key] = $violation->getMessage();
        }

        throw new UnprocessableEntityHttpException(json_encode([
            'data' => [
                'errors' => $validationErrors
            ]
        ]));
    }
}