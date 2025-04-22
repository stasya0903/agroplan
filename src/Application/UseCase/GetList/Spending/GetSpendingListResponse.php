<?php

namespace App\Application\UseCase\GetList\Spending;

class GetSpendingListResponse
{
    public function __construct(
        public iterable $spending,
        public float $total
    ) {
    }
}
