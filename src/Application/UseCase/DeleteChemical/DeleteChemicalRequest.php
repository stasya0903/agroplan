<?php

namespace App\Application\UseCase\DeleteChemical;

class DeleteChemicalRequest
{
    public function __construct(
        public readonly int $id
    ) {
    }
}
