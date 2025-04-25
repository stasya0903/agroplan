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
                s.id, sg.date, s.plantation_id, sg.type, s.amount_in_cents, sg.note, 
                pl.name as plantation_name
                FROM spending s
                LEFT JOIN plantations pl ON s.plantation_id = pl.id 
                LEFT JOIN spending_group sg ON s.spending_group_id = sg.id 
                ';
        $params = [];
        $types = [];

        if ($query->getSpendingTypeId() !== null) {
            $sql .= ' WHERE sg.type = :typeId';
            $params['typeId'] = $query->getSpendingTypeId();
            $types['typeId'] = Types::INTEGER;
        }

        if ($query->getPlantationId() !== null) {
            $sql .= count($params) ? ' AND s.plantation_id = :plantationId' : ' WHERE s.plantation_id = :plantationId';
            $params['plantationId'] = $query->getPlantationId();
            $types['plantationId'] = Types::STRING;
        }

        if ($query->getDateFrom() !== null) {
            $sql .= count($params) ? ' AND sg.date >= :dateFrom' : ' WHERE sg.date >= :dateFrom';
            $params['dateFrom'] = $query->getDateFrom()->getValue();
            $types['dateFrom'] = Types::DATETIME_IMMUTABLE;
        }

        if ($query->getDateTo() !== null) {
            $sql .= count($params) ? ' AND sg.date <= :dateTo' : ' WHERE sg.date <= :dateTo';
            $params['dateTo'] = $query->getDateTo()->getValue();
            $types['dateTo'] = Types::DATETIME_IMMUTABLE;
        }

        $result = $this->db->fetchAllAssociative($sql, $params, $types);
        return array_map(fn($row) => new SpendingDTO(
            $row['id'],
            $row['plantation_id'],
            $row['plantation_name'],
            (new Money($row['amount_in_cents']))->getAmountAsFloat(),
            SpendingType::from($row['type'])->label(),
            $row['date'],
            $row['note'],
        ), $result);
    }
}
