<?php

namespace App\Domain\Entity;

use App\Domain\SystemWorkType;
use App\Domain\ValueObject\Name;

class WorkType
{
    private ?int $id = null;

    public function __construct(
        public readonly Name $name,
        public readonly bool $isSystem = false,
    ) {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): Name
    {
        return $this->name;
    }

    public function isSystem(): bool
    {
        return $this->id && SystemWorkType::isSystemId($this->id);
    }
}
