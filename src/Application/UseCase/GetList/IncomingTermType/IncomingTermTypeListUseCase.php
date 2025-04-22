<?php

namespace App\Application\UseCase\GetList\IncomingTermType;

use App\Application\UseCase\GetList\IncomingTermType\IncomingTermTypeListResponse;
use App\Domain\Enums\IncomingTermType;

class IncomingTermTypeListUseCase
{
    public function __invoke(): IncomingTermTypeListResponse
    {
        $options = IncomingTermType::options();
        return new IncomingTermTypeListResponse($options);
    }
}
