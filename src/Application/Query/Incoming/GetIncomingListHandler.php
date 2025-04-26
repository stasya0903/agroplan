<?php

namespace App\Application\Query\Incoming;

use App\Application\DTO\IncomingDTO;
use App\Domain\Enums\IncomingTermType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Weight;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

class GetIncomingListHandler
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(GetIncomingListQuery $query): array
    {
        $sql = 'SELECT i.* , pl.name as plantation_name
                FROM incoming i
                LEFT JOIN plantations pl ON i.plantation_id = pl.id 
                ';
        $params = [];
        $types = [];

        if ($query->getPlantationId() !== null) {
            $sql .= count($params) ? ' AND i.plantation_id = :plantationId' : ' WHERE i.plantation_id = :plantationId';
            $params['plantationId'] = $query->getPlantationId();
            $types['plantationId'] = Types::STRING;
        }

        if ($query->getDateFrom() !== null) {
            $sql .= count($params) ? ' AND i.date >= :dateFrom' : ' WHERE i.date >= :dateFrom';
            $params['dateFrom'] = $query->getDateFrom()->getValue();
            $types['dateFrom'] = Types::DATETIME_IMMUTABLE;
        }

        if ($query->getDateTo() !== null) {
            $sql .= count($params) ? ' AND i.date <= :dateTo' : ' WHERE i.date <= :dateTo';
            $params['dateTo'] = $query->getDateTo()->getValue();
            $types['dateTo'] = Types::DATETIME_IMMUTABLE;
        }

        $sql .= ' ORDER BY i.date';
        $result = $this->db->fetchAllAssociative($sql, $params, $types);
        return array_map(fn($row) => new IncomingDTO(
            $row['id'],
            $row['date'],
            $row['plantation_id'],
            $row['plantation_name'],
            (new Money($row['amount_in_cents']))->getAmountAsFloat(),
            $row['note'],
            Weight::createFromGrams($row['weight_in_grams'])->getKg(),
            $row['type'],
            IncomingTermType::from($row['type'])->label(),
            $row['buyer_name'],
            (new Money($row['price_in_cents']))->getAmountAsFloat(),
            $row['paid'],
            $row['paid'] ? new Date($row['date_paid']) : null
        ), $result);
    }
}
