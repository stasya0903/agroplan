<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateWork\CreateWorkRequest;
use App\Application\UseCase\CreateWork\CreateWorkUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/work/add',
    name: 'work_add',
    methods: ['POST']
)]
final class CreateWorkController extends AbstractController
{
    public function __construct(
        private readonly CreateWorkUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateWorkRequest $request): Response
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
