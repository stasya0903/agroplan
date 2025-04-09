<?php

namespace App\Application\Query;

use App\Application\DTO\NewsDTO;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

class GetNewsHandler
{
    public function __construct(private readonly Connection $db)
    {
    }
    public function handle(GetNewsQuery $query): array
    {
        $ids = $query->getIds();
        if (count($ids) === 0) {
            $result = $this->db->fetchAllAssociative(
                'SELECT id, url, title, created_at FROM news'
            );
        } else {
            $result = $this->db->fetchAllAssociative(
                'SELECT id, url, title, created_at FROM news WHERE id IN (?)',
                [$ids],
                [ArrayParameterType::INTEGER]
            );
        }
        return array_map(fn($news) => new NewsDTO(
            $news['id'],
            new \DateTimeImmutable($news['created_at']),
            $news['url'],
            $news['title'],
        ), $result);
    }
}
