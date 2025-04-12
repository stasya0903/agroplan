<?php

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\CreateWorker\CreateWorkerRequest;
use Symfony\Component\Validator\Constraints as Assert;

final class HttpCreateWorkerRequest
{
    #[Assert\NotBlank]
    #[Assert\Type('float')]
    public mixed $dailyRate;

    #[Assert\NotBlank]
    public mixed $name;

    public function toRequest(): CreateWorkerRequest
    {
        return new CreateWorkerRequest(
            name: $this->name,
            dailyRate: $this->dailyRate,
        );
    }
}