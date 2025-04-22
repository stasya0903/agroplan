<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\EditWorkerShift\EditWorkerShiftRequest;
use App\Application\UseCase\EditWorkerShift\EditWorkerShiftUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(
    '/api/v1/worker_shift/edit',
    name: 'worker_shift_edit',
    methods: ['POST']
)]
final class EditWorkerShiftController extends AbstractController
{
    public function __construct(
        private readonly EditWorkerShiftUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditWorkerShiftRequest $request): Response
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
