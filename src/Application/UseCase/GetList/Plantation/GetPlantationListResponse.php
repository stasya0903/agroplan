<?php

namespace App\Application\UseCase\GetList\Plantation;

class GetPlantationListResponse
{
    public function __construct(
        public iterable $plantations
    ) {
    }
}