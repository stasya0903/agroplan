<?php

namespace App\Application\UseCase\GetBudget;

class GetBudgetRequest
{
    public function __construct(
        public ?int $plantationId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {
    }
}
