<?php

namespace App\Application\UseCase\GetBudget;

class GetBudgetResponse
{
    public function __construct(
        public array $incoming,
        public array $spending,
        public float $totalSpend,
        public float $totalIncome,
        public float $profit,

    ) {
    }
}
