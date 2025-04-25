<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\UseCase\EditSpendingGroup\EditSpendingGroupRequest;
use App\Application\UseCase\EditSpendingGroup\EditSpendingGroupUseCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route(
    '/api/v1/spending_group/edit',
    name: 'spending_group_edit',
    methods: ['POST']
)]
final class EditSpendingGroupController extends AbstractController
{
    public function __construct(
        private readonly EditSpendingGroupUseCase $useCase
    ) {
    }

    public function __invoke(#[MapRequestPayload] EditSpendingGroupRequest $request): Response
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
