<?php

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\GetList\Plantation\GetPlantationListRequest;
use Symfony\Component\Validator\Constraints as Assert;

class HttpGetPlantationListRequest
{
    #[Assert\Type('array')]
    #[Assert\All([
        new Assert\Type('integer'),
    ])]
    public ?array $ids = [];

    public function toApplicationRequest(): GetPlantationListRequest
    {
        return new GetPlantationListRequest($this->ids ?? []);
    }
}
