<?php

namespace App\Domain\Factory;

use App\Domain\Entity\Work;
use App\Domain\Entity\WorkType;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;

interface WorkFactoryInterface
{
    public function create(
        int $workTypeId,
        int $plantationId,
        \DateTimeInterface $date,
        array $workerIds,
        Note $note
    ): Work;
}
