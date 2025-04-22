<?php

namespace App\Application\UseCase\GetList\WorkType;

class GetWorkTypeListResponse
{
    public function __construct(
        public iterable $workTypes
    ) {
    }
}
