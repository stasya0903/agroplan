<?php

namespace App\Application\Query\SpendingGroup;

use App\Application\DTO\SpendingDTO;
use App\Application\DTO\SpendingGroupDTO;
use App\Domain\Enums\SpendingType;
use App\Domain\ValueObject\Money;

use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Types\Types;

class GetSpendingGroupListHandler
{
    public function __construct(private readonly Connection $db)
    {
    }

    /**
     * @throws Exception
     */
    public function handle(GetSpendingGroupListQuery $query): array
    {
        $sql = 'SELECT 
                sg.id, sg.date, sg.type, sg.amount_in_cents, sg.note
                FROM spending_group sg
                ';
        $params = [];
        $types = [];

        if ($query->getSpendingGroupTypeId() !== null) {
            $sql .= ' WHERE sg.type = :typeId';
            $params['typeId'] = $query->getSpendingGroupTypeId();
            $types['typeId'] = Types::INTEGER;
        }

        // STEP 1: Get group IDs filtered by plantation
        $groupIds = null;
        if ($query->getPlantationId() !== null) {
            $groupIdSql = 'SELECT DISTINCT s.spending_group_id 
                       FROM spending s 
                       WHERE s.spending_group_id IS NOT NULL 
                       AND s.plantation_id = :plantationId';

            $paramsPlantation['plantationId'] = $query->getPlantationId();
            $typesPlantation['plantationId'] = Types::INTEGER;

            $groupIdRows = $this->db->fetchFirstColumn($groupIdSql, $paramsPlantation, $typesPlantation);
            if (empty($groupIdRows)) {
                return []; // No matching groups found
            }

            $sql .= count($params) ? ' AND sg.id IN (:groupIds)' : ' WHERE sg.id IN (:groupIds)';
            $params['groupIds'] = $groupIdRows;
            $types['groupIds'] = ArrayParameterType::INTEGER;
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


        $groups = $this->db->fetchAllAssociative($sql, $params, $types);
        $spendingGroupIds = array_column($groups, 'id');

        $spendingSql = 'SELECT s.id, s.spending_group_id, s.plantation_id, p.name AS plantation_name, s.amount_in_cents 
                    FROM spending s
                    LEFT JOIN plantations p ON s.plantation_id = p.id
                    WHERE s.spending_group_id IN (:groupIds)';


        $spendingParams = ['groupIds' => $spendingGroupIds];
        $spendingTypes = ['groupIds' => ArrayParameterType::INTEGER];
        if ($query->getPlantationId() !== null) {
            $spendingSql .= ' AND s.plantation_id = :plantationId';
            $spendingParams['plantationId'] = $query->getPlantationId();
            $spendingTypes['plantationId'] = Types::STRING;
        }
        $spending = $this->db->fetchAllAssociative($spendingSql, $spendingParams, $spendingTypes);
        $spendingByGroup = [];
        foreach ($spending as $s) {
            $spendingByGroup[$s['spending_group_id']][] = new SpendingDTO(
                $s['id'],
                $s['plantation_id'],
                $s['plantation_name'],
                (new Money($s['amount_in_cents']))->getAmountAsFloat()
            );
        }
        return array_map(fn($row) => new SpendingGroupDTO(
            $row['id'],
            $row['date'],
            $row['type'],
            SpendingType::from($row['type'])->label(),
            (new Money($row['amount_in_cents']))->getAmountAsFloat(),
            $row['note'],
            $spendingByGroup[$row['id']] ?? [],
        ), $groups);
    }
}
