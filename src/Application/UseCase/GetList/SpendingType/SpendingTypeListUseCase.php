<?php

namespace App\Application\UseCase\GetList\SpendingType;

use App\Domain\Enums\SpendingType;

class SpendingTypeListUseCase
{
    public function __invoke(): SpendingTypeListResponse
    {
        $options = SpendingType::options();
        return new SpendingTypeListResponse($options);
    }
}
