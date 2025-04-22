<?php

namespace App\Application\UseCase\GetList\Work;

class GetWorkListResponse
{
    public function __construct(
        public iterable $works
    ) {
    }
}
