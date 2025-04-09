<?php

namespace App\Application\Command;

use App\Domain\Entity\News;

class CreateNewsCommand
{
    public function __construct(
        private readonly string $url,
        private readonly string $title,
    ) {
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function getTitle(): string
    {
        return $this->title;
    }
}
