<?php

namespace App\Infrastructure\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\WorkType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkFactoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\PlantationName;

class WorkFactory implements WorkFactoryInterface
{

    public function create(
        WorkType $workType,
        Plantation $plantation,
        Date $date,
        array $workerIds,
        Note $note
    ): Work
    {
        return new Work($workType, $plantation, $date, $workerIds, $note);
    }
}
