<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\CreateIncoming\CreateIncomingRequest;
use App\Application\UseCase\CreateIncoming\CreateIncomingUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/incoming/add',
    name: 'incoming_add',
    methods: ['POST']
)]
final class CreateIncomingController extends AbstractController
{
    public function __construct(
        private readonly CreateIncomingUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] CreateIncomingRequest $request): Response
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
