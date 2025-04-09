<?php

namespace App\Application\DTO;

class NewsDTO
{
    public function __construct(
        public int $id,
        public \DateTimeImmutable $date,
        public string $url,
        public string $title
    ) {
    }
}
