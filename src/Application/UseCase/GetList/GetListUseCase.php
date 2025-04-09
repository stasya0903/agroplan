<?php

namespace App\Application\UseCase\GetList;

use App\Application\DTO\GetListResponseDTO;
use App\Application\Query\GetNewsHandler;
use App\Application\Query\GetNewsQuery;

class GetListUseCase
{
    public function __construct(
        private readonly GetNewsHandler $getNewsHandler,
    ) {
    }

    public function __invoke(): iterable
    {
        $query = new GetNewsQuery();
        return  $this->getNewsHandler->handle($query);
    }
}
