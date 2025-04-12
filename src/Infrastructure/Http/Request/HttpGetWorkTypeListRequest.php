<?php

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\GetList\WorkType\GetWorkTypeListRequest;
use Symfony\Component\Validator\Constraints as Assert;

class HttpGetWorkTypeListRequest
{
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public ?array $ids = [];

    public function toApplicationRequest(): GetWorkTypeListRequest
    {
        return new GetWorkTypeListRequest($this->ids ?? []);
    }
}
