<?php

namespace App\Application\UseCase\DeleteChemical;

class DeleteChemicalResponse
{
    public function __construct(
        public bool $result
    ) {
    }
}
