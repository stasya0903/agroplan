<?php

namespace App\Application\UseCase\GetList\WorkType;

class GetWorkTypeListRequest
{
    public function __construct(
        public ?array $ids = []
    ) {
    }
}
