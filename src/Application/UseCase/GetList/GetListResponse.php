<?php

namespace App\Application\UseCase\GetList;

class GetListResponse
{
    public function __construct(
        public iterable $news
    ) {
    }
}
