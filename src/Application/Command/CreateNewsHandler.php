<?php

namespace App\Application\Command;

use App\Domain\Entity\News;
use App\Domain\Factory\NewsFactoryInterface;
use App\Domain\Repository\NewsRepositoryInterface;
use App\Domain\ValueObject\Title;
use App\Domain\ValueObject\Url;

class CreateNewsHandler
{
    public function __construct(
        private readonly NewsRepositoryInterface $newsRepository,
        private readonly NewsFactoryInterface $newsFactory
    ) {
    }

    public function handle(CreateNewsCommand $command): News
    {
        $url = new Url($command->getUrl());
        $title = new Title($command->getTitle());
        $news = $this->newsFactory->create($url, $title);
        $this->newsRepository->save($news);
        return $news;
    }
}
