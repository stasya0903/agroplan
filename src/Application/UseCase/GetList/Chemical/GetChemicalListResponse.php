<?php

namespace App\Application\UseCase\GetList\Chemical;

class GetChemicalListResponse
{
    public function __construct(
        public iterable $chemicals
    ) {
    }
}
