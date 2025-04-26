<?php

namespace App\Application\Query\Work;

use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerDTO;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

class GetWorkListHandler
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(GetWorkListQuery $query): array
    {
        $sql = 'SELECT w.id, w.date, w.note, w.plantation_id, w.work_type_id, pl.name as plantation_name, wt.name as work_type_name 
                FROM work w 
                LEFT JOIN plantations pl ON w.plantation_id = pl.id 
                LEFT JOIN work_types wt ON w.work_type_id = wt.id';
        $params = [];
        $types = [];

        if ($query->getWorkTypeId() !== null) {
            $sql .= ' WHERE w.work_type_id = :workTypeId';
            $params['workTypeId'] = $query->getWorkTypeId();
            $types['workTypeId'] = Types::INTEGER;
        }

        if ($query->getPlantationId() !== null) {
            $sql .= count($params) ? ' AND w.plantation_id = :plantationId' : ' WHERE w.plantation_id = :plantationId';
            $params['plantationId'] = $query->getPlantationId();
            $types['plantationId'] = Types::STRING;
        }

        if ($query->getDateFrom() !== null) {
            $sql .= count($params) ? ' AND w.date >= :dateFrom' : ' WHERE w.date >= :dateFrom';
            $params['dateFrom'] = $query->getDateFrom()->getValue();
            $types['dateFrom'] = Types::DATETIME_IMMUTABLE;
        }

        if ($query->getDateTo() !== null) {
            $sql .= count($params) ? ' AND w.date <= :dateTo' : ' WHERE w.date <= :dateTo';
            $params['dateTo'] = $query->getDateTo()->getValue();
            $types['dateTo'] = Types::DATETIME_IMMUTABLE;
        }
        $sql .= ' ORDER BY w.date';

        $result = $this->db->fetchAllAssociative($sql, $params, $types);
        $workIds = array_column($result, 'id');

        $workerSql = "
            SELECT ww.work_entity_id AS work_id, u.id AS worker_id, u.name AS worker_name, u.daily_rate_in_cents
            FROM work_worker ww
            JOIN workers u ON u.id = ww.worker_entity_id
            WHERE ww.work_entity_id IN (?)";

        $workerRows = $this->db->fetchAllAssociative(
            $workerSql,
            [$workIds],
            [ArrayParameterType::INTEGER]
        );
        $workersByWorkId = [];
        foreach ($workerRows as $row) {
            $workersByWorkId[$row['work_id']][] = new WorkerDTO(
                $row['worker_id'],
                $row['worker_name'],
                $row['daily_rate_in_cents']
            );
        }
        return array_map(fn($row) => new WorkDTO(
            $row['id'],
            $row['work_type_id'],
            $row['work_type_name'],
            $row['plantation_id'],
            $row['plantation_name'],
            $row['date'],
            $workersByWorkId[$row['id']] ?? [],
            $row['note'],
        ), $result);
    }
}
