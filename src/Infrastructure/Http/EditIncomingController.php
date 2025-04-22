<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\EditIncoming\EditIncomingRequest;
use App\Application\UseCase\EditIncoming\EditIncomingUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/incoming/edit',
    name: 'incoming_edit',
    methods: ['POST']
)]
final class EditIncomingController extends AbstractController
{
    public function __construct(
        private readonly EditIncomingUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditIncomingRequest $request): Response
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
