<?php

namespace App\Application\UseCase\GetList\Plantation;

class GetPlantationListRequest
{
    public function __construct(
        public ?array $ids = []
    ) {
    }
}
