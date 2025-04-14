<?php

namespace App\Domain\Entity;

use App\Domain\Enums\SystemWorkType;
use App\Domain\ValueObject\Name;

class WorkType
{
    private ?int $id = null;

    public function __construct(
        public Name $name,
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
    public function rename(Name $param): void
    {
        $this->name = $param;
    }
}
