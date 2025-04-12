<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateWorker\CreateWorkerRequest;
use App\Application\UseCase\CreateWorker\CreateWorkerUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/worker/add',
    name: 'worker_add',
    methods: ['POST']
)]
final class CreateWorkerController extends AbstractController
{
    public function __construct(
        private readonly CreateWorkerUseCase $useCase,
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateWorkerRequest $request): Response
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
