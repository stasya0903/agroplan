<?php

namespace App\Infrastructure\Validation;

namespace App\Infrastructure\Validation;

use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ValidationService
{
    public function __construct(private readonly ValidatorInterface $validator) {}

    public function validate(object $dto): array
    {
        $violations = $this->validator->validate($dto);

        return  $this->formatViolations($violations);
    }

    private function formatViolations(ConstraintViolationListInterface $violations): array
    {
        $errors = [];

        foreach ($violations as $violation) {
            $errors[] = [
                'field' => $violation->getPropertyPath(),
                'message' => $violation->getMessage(),
            ];
        }

        return $errors;
    }
}
