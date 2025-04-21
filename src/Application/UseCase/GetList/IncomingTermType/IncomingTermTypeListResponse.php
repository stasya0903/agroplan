<?php

namespace App\Application\UseCase\GetList\IncomingTermType;

class IncomingTermTypeListResponse
{
    public function __construct(
        public array $incomingTermTypes
    ) {
    }
}
