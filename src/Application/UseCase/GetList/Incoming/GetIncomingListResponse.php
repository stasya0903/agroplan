<?php

namespace App\Application\UseCase\GetList\Incoming;

class GetIncomingListResponse
{
    public function __construct(
        public array $incoming,
        public float $total,
        public float $averagePrice,
    ) {
    }
}
