<?php

namespace App\Application\UseCase\GetList\Incoming;

class GetIncomingListRequest
{
    public function __construct(
        public ?int $plantationId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {
    }
}
