<?php

namespace App\Application\Query\WorkShift;

use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerDTO;
use App\Application\DTO\WorkerShiftDTO;
use App\Application\Query\Work\GetWorkListQuery;
use App\Domain\ValueObject\Money;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

class GetWorkerShiftListHandler
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(GetWorkerShiftListQuery $query): array
    {
        $sql = 'SELECT 
                ws.id, ws.date, ws.plantation_id, ws.worker_id, ws.payment_in_cents, ws.paid, 
                pl.name as plantation_name, 
                wr.name as worker_name, wr.daily_rate_in_cents
                FROM worker_shift ws 
                LEFT JOIN plantations pl ON ws.plantation_id = pl.id 
                LEFT JOIN workers wr ON ws.worker_id = wr.id';
        $params = [];
        $types = [];

        if ($query->getWorkerId() !== null) {
            $sql .= ' WHERE ws.worker_id = :workerId';
            $params['workerId'] = $query->getWorkerId();
            $types['workerId'] = Types::INTEGER;
        }

        if ($query->getPlantationId() !== null) {
            $sql .= count($params) ? ' AND ws.plantation_id = :plantationId' : ' WHERE ws.plantation_id = :plantationId';
            $params['plantationId'] = $query->getPlantationId();
            $types['plantationId'] = Types::STRING;
        }

        if ($query->getDateFrom() !== null) {
            $sql .= count($params) ? ' AND ws.date >= :dateFrom' : ' WHERE ws.date >= :dateFrom';
            $params['dateFrom'] = $query->getDateFrom()->getValue();
            $types['dateFrom'] = Types::DATETIME_IMMUTABLE;
        }

        if ($query->getDateTo() !== null) {
            $sql .= count($params) ? ' AND ws.date <= :dateTo' : ' WHERE ws.date <= :dateTo';
            $params['dateTo'] = $query->getDateTo()->getValue();
            $types['dateTo'] = Types::DATETIME_IMMUTABLE;
        }
        if ($query->getPaid() !== null) {
            $sql .= count($params) ? ' AND ws.paid = :paid' : ' WHERE ws.paid = :paid';
            $params['paid'] = $query->getPaid();
            $types['paid'] = Types::BOOLEAN;
        }

        $result = $this->db->fetchAllAssociative($sql, $params, $types);
        return array_map(fn($row) => new WorkerShiftDTO(
            $row['id'],
            $row['date'],
            $row['plantation_id'],
            $row['plantation_name'],
            $row['worker_id'],
            $row['worker_name'],
            (new Money($row['daily_rate_in_cents']))->getAmountAsFloat(),
            (new Money($row['payment_in_cents']))->getAmountAsFloat(),
            $row['paid'],
        ), $result);
    }
}
