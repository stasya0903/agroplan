<?php

namespace App\Application\UseCase\CreateWork;

use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Entity\Recipe;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Factory\RecipeFactoryInterface;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Factory\SpendingGroupFactoryInterface;
use App\Domain\Factory\WorkerShiftFactoryInterface;
use App\Domain\Factory\WorkFactoryInterface;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\ProblemTypeRepositoryInterface;
use App\Domain\Repository\RecipeRepositoryInterface;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Volume;

class CreateWorkUseCase
{

    public function __construct(
        private readonly WorkFactoryInterface $factory,
        private readonly WorkerShiftFactoryInterface $workerShiftFactory,
        private readonly WorkRepositoryInterface $repository,
        private readonly PlantationRepositoryInterface $plantationRepository,
        private readonly WorkTypeRepositoryInterface $workTypeRepository,
        private readonly WorkerRepositoryInterface $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingFactoryInterface $spendingFactory,
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly TransactionalSessionInterface $transaction,
        private readonly SpendingGroupFactoryInterface $spendingGroupFactory,
        private readonly SpendingGroupRepositoryInterface $spendingGroupRepository,
        private readonly ProblemTypeRepositoryInterface $problemRepository,
        private readonly ChemicalRepositoryInterface $chemicalRepository,
        private readonly RecipeRepositoryInterface $recipeRepository,
        private readonly RecipeFactoryInterface $recipeFactory,
    ) {
    }

    public function __invoke(CreateWorkRequest $request): CreateWorkResponse
    {
        $workType = $this->workTypeRepository->find($request->workTypeId);
        if (!$workType) {
            throw new \DomainException('Work type not found.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }

        $workers = [];
        $workerIds = $request->workerIds;
        foreach ($workerIds as $workerId) {
            $worker = $this->workerRepository->find($workerId);
            if (!$worker) {
                throw new \DomainException('Worker not found.');
            } else {
                $workers[] = $worker;
            }
        }
        if($workType->getId() !== SystemWorkType::FERTILIZATION->value && $request->recipe){
            throw new \DomainException('Recipe can be added only for FERTILIZATION work.');
        }
        $recipe = null;
        if($workType->getId() === SystemWorkType::FERTILIZATION->value){
            if(empty($request->recipe)){
                throw new \DomainException('Recipe is required for FERTILIZATION work.');
            }
            $recipeItem = $request->recipe;
            $chemical = $this->chemicalRepository->find($recipeItem->chemicalId);
            if (!$chemical) {
                throw new \DomainException('Chemical not found.');
            }

            $problem = null;
            if ($recipeItem->problemId) {
                $problem = $this->problemRepository->find($recipeItem->problemId);
                if (!$problem) {
                    throw new \DomainException('Problem not found.');
                }
            }

            $recipe = $this->recipeFactory->create(
                $chemical,
                new Volume($recipeItem->dosis),
                $problem,
                $recipeItem->note
            );
        }
        return $this->transaction->run(function () use ($recipe, $workers, $plantation, $workType, $request) {
            $date = new Date($request->date);
            $work = $this->factory->create(
                $workType,
                $plantation,
                $date,
                $workers,
                new Note($request->note)
            );
            $this->repository->save($work);

            if($recipe){
                $recipe->assignWork($work);
                $this->recipeRepository->save($recipe);
            }

            foreach ($workers as $worker) {
                $workerShift = $this->workerShiftFactory->create(
                    $worker,
                    $plantation,
                    $date,
                    $worker->getDailyRate()
                );
                $workerShift->assignToWork($work);
                $this->workerShiftRepository->save($workerShift);
            }

            $price = $work->getFullPrice();
            if ($price > 0) {
                $spendingGroup = $this->spendingGroupFactory->create(
                    SpendingType::WORK,
                    $date,
                    new Money($price),
                    new Note('From work'),
                    false
                );
                $spendingGroup->assignToWork($work);
                $this->spendingGroupRepository->save($spendingGroup);
                $spending = $this->spendingFactory->create(
                    $spendingGroup,
                    $plantation,
                    new Money($price),
                );
                $this->spendingRepository->save($spending);
            }

            return new CreateWorkResponse($work->getId());
        });
    }
}
