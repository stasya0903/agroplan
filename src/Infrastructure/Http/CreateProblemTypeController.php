<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateProblemType\CreateProblemTypeRequest;
use App\Application\UseCase\CreateProblemType\CreateProblemTypeUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/problem_type/add',
    name: 'problemType_add',
    methods: ['POST']
)]
final class CreateProblemTypeController extends AbstractController
{
    public function __construct(
        private readonly CreateProblemTypeUseCase $useCase,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateProblemTypeRequest $request): Response
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
