<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\WorkType;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;

interface WorkFactoryInterface
{
    public function create(
        WorkType $workType,
        Plantation $plantation,
        Date $date,
        array $workerIds,
        Note $note
    ): Work;
}
