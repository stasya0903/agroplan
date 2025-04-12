<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Request;

use App\Application\UseCase\CreateWorkType\CreateWorkTypeRequest;
use App\Application\UseCase\CreateWorkType\CreateWorkTypeUseCase;
use App\Infrastructure\Http\Request\HttpCreateWorkTypeRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/v1/work_type/add',
    name: 'work_type_add',
    methods: ['POST']
)]
final class CreateWorkTypeController extends AbstractController
{
    public function __construct(
        private readonly CreateWorkTypeUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateWorkTypeRequest $request): Response
    {
        try {
            $response = ($this->useCase)($request);
            return $this->json($response);
        } catch (\Throwable $e) {
            $errorResponse = [
                'message' => $e->getMessage()
            ];
            return $this->json($errorResponse, 400);
        }
    }
}
