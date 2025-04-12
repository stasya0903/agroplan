<?php

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\GetList\Worker\GetWorkerListRequest;
use Symfony\Component\Validator\Constraints as Assert;

class HttpGetWorkerListRequest
{
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public ?array $ids = [];

    public function toApplicationRequest(): GetWorkerListRequest
    {
        return new GetWorkerListRequest($this->ids ?? []);
    }
}
