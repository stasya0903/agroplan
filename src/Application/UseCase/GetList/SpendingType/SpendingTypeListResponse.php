<?php

namespace App\Application\UseCase\GetList\SpendingType;

class SpendingTypeListResponse
{
    public function __construct(
        public iterable $spendingTypes
    ) {
    }
}
