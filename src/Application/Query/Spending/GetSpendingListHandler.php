<?php

namespace App\Application\Query\Spending;

use App\Application\DTO\SpendingDTO;
use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerDTO;
use App\Application\DTO\WorkerShiftDTO;
use App\Application\Query\Work\GetWorkListQuery;
use App\Application\Query\WorkShift\GetWorkerShiftListQuery;
use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

class GetSpendingListHandler
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(GetSpendingListQuery $query): array
    {
        $sql = 'SELECT 
                s.id, s.date, s.plantation_id, s.type, s.amount_in_cents, s.note, 
                pl.name as plantation_name
                FROM spending s
                LEFT JOIN plantations pl ON s.plantation_id = pl.id 
                 where 1 = 1';
        $params = [];
        $types = [];

        if ($query->getSpendingTypeId() !== null) {
            $sql .= ' AND s.type = :typeId';
            $params['typeId'] = $query->getSpendingTypeId();
            $types['typeId'] = Types::INTEGER;
        }

        if ($query->getPlantationId() !== null) {
            $sql .= ' AND s.plantation_id = :plantationId';
            $params['plantationId'] = $query->getPlantationId();
            $types['plantationId'] = Types::STRING;
        }

        if ($query->getDateFrom() !== null) {
            $sql .= ' AND s.date >= :dateFrom';
            $params['dateFrom'] = $query->getDateFrom()->getValue();
            $types['dateFrom'] = Types::DATETIME_IMMUTABLE;
        }

        if ($query->getDateTo() !== null) {
            $sql .= ' AND s.date <= :dateTo';
            $params['dateTo'] = $query->getDateTo()->getValue();
            $types['dateTo'] = Types::DATETIME_IMMUTABLE;
        }

        $result = $this->db->fetchAllAssociative($sql, $params, $types);
        return array_map(fn($row) => new SpendingDTO(
            $row['id'],
            $row['date'],
            $row['plantation_id'],
            $row['plantation_name'],
            $row['type'],
            SpendingType::from($row['type'])->label(),
            (new Money($row['amount_in_cents']))->getAmountAsFloat(),
            $row['note'],
        ), $result);
    }
}
