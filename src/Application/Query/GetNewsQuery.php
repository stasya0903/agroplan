<?php

namespace App\Application\Query;

class GetNewsQuery
{
    public function __construct(private readonly iterable $ids = [])
    {
    }

    public function getIds(): array
    {
        return $this->ids;
    }
}
