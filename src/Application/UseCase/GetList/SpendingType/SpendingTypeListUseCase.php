<?php

namespace App\Application\UseCase\GetList\SpendingType;

use App\Domain\Enums\SpendingType;

class SpendingTypeListUseCase
{
    public function __invoke(): SpendingTypeListResponse
    {
        $options = SpendingType::options();
        $options = array_filter($options, function ($option) {
            return $option['value'] !== SpendingType::WORK->value;
        });
        return new SpendingTypeListResponse($options);
    }
}
