<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\EditWork\EditWorkRequest;
use App\Application\UseCase\EditWork\EditWorkUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/work/edit',
    name: 'work_edit',
    methods: ['POST']
)]
final class EditWorkController extends AbstractController
{
    public function __construct(
        private readonly EditWorkUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditWorkRequest $request): Response
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
