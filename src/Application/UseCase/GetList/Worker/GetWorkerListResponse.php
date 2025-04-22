<?php

namespace App\Application\UseCase\GetList\Worker;

class GetWorkerListResponse
{
    public function __construct(
        public iterable $workers
    ) {
    }
}
