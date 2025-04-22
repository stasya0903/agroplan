<?php

namespace App\Application\UseCase\GetList\Worker;

class GetWorkerListRequest
{
    public function __construct(
        public ?array $ids = []
    ) {
    }
}
