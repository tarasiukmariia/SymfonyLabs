<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Validator\ConstraintViolationList;
use Throwable;

class RuntimeConstraintExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $code = $this->getCode($exception);
        $errors = $this->getErrors($exception);

        $event->setResponse(new JsonResponse([
            "data" => [
                "code" => $code,
                "errors" => $errors
            ]
        ], $code));
    }

    public function getCode(Throwable $exception): int
    {
        if (method_exists($exception, "getStatusCode")) {
            return array_key_exists($exception->getStatusCode(), Response::$statusTexts) 
                ? $exception->getStatusCode() 
                : Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        return array_key_exists($exception->getCode(), Response::$statusTexts) 
            ? $exception->getCode() 
            : Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrors(Throwable $exception): array
    {
        $errors = [];

        if (method_exists($exception, "getConstraintViolationList")) {
            return $this->getAssociativeErrorsForConstraintViolationList(
                $exception->getConstraintViolationList(), 
                $errors
            );
        }

        if ($tmpErrors = json_decode($exception->getMessage(), true)) {
            return $this->getAssociativeErrors(
                $tmpErrors["data"]["errors"] ?? $tmpErrors, 
                $errors
            );
        }

        $errors[] = [$exception->getMessage()];
        
        return $errors;
    }

    public function getAssociativeErrors(array $tmpErrors, array $errors): array
    {
        foreach ($tmpErrors as $key => $error) {
            if (is_array($error)) {
                $errors[$key] = $this->getAssociativeErrors($error, $errors);
            } else {
                $errors[$key] = $error;
            }
        }
        
        return $errors;
    }

    public function getAssociativeErrorsForConstraintViolationList(ConstraintViolationList $list, array $errors): array
    {
        foreach ($list as $key => $error) {
            $errors[$key][$error->getPropertyPath()] = $error->getMessage();
        }
        
        return $errors;
    }
}