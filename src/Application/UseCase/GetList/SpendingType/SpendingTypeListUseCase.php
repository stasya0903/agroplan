<?php

namespace App\Application\UseCase\GetList\SpendingType;


use App\Domain\Enums\SpendingType;

class SpendingTypeListUseCase
{
    public function __invoke(): SpendingTypeListResponse
    {
        return new SpendingTypeListResponse(
             SpendingType::options()
        );
    }
}
