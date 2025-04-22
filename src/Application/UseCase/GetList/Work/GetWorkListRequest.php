<?php

namespace App\Application\UseCase\GetList\Work;

class GetWorkListRequest
{
    public function __construct(
        public ?int $workTypeId = null,
        public ?int $plantationId = null,
        public ?string $dateFrom = null,
        public ?string $dateTo = null
    ) {
    }
}
