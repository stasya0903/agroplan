<?php

namespace App\Application\UseCase\EditWork;

use App\Application\DTO\RecipeDTO;
use App\Application\DTO\WorkDTO;
use App\Application\DTO\WorkerDTO;
use App\Application\Shared\TransactionalSessionInterface;
use App\Domain\Entity\Recipe;
use App\Domain\Entity\Work;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Factory\RecipeFactoryInterface;
use App\Domain\Factory\SpendingFactoryInterface;
use App\Domain\Factory\SpendingGroupFactoryInterface;
use App\Domain\Factory\WorkerShiftFactoryInterface;
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

class EditWorkUseCase
{
    public function __construct(
        private readonly WorkerShiftFactoryInterface $workerShiftFactory,
        private readonly WorkRepositoryInterface $repository,
        private readonly PlantationRepositoryInterface $plantationRepository,
        private readonly WorkTypeRepositoryInterface $workTypeRepository,
        private readonly WorkerRepositoryInterface $workerRepository,
        private readonly WorkerShiftRepositoryInterface $workerShiftRepository,
        private readonly SpendingGroupRepositoryInterface $spendingGroupRepository,
        private readonly SpendingGroupFactoryInterface $spendingGroupFactory,
        private readonly TransactionalSessionInterface $transaction,
        private readonly SpendingRepositoryInterface $spendingRepository,
        private readonly SpendingFactoryInterface $spendingFactory,
        private readonly ProblemTypeRepositoryInterface $problemRepository,
        private readonly ChemicalRepositoryInterface $chemicalRepository,
        private readonly RecipeRepositoryInterface $recipeRepository,
        private readonly RecipeFactoryInterface $recipeFactory,
    ) {
    }

    public function __invoke(EditWorkRequest $request): EditWorkResponse
    {
        $work = $this->repository->findWithAllData($request->workId);

        if (!$work) {
            throw new \DomainException('Work not found.');
        }
        $workType = $this->workTypeRepository->find($request->workTypeId);
        if (!$workType) {
            throw new \DomainException('Work type not found.');
        }
        $plantation = $this->plantationRepository->find($request->plantationId);
        if (!$plantation) {
            throw new \DomainException('Plantation not found.');
        }
        if ($workType->getId() !== SystemWorkType::FERTILIZATION->value && $request->recipe) {
            throw new \DomainException('Recipe can be added only for FERTILIZATION work.');
        }
        $recipe = null;
        if ($workType->getId() === SystemWorkType::FERTILIZATION->value) {
            if (empty($request->recipe)) {
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
        return $this->transaction->run(function () use ($recipe, $work, $workers, $plantation, $workType, $request) {
            $date = new Date($request->date);

            $work->setWorkType($workType);
            $work->setPlantation($plantation);
            $work->setDate($date);
            $work->setWorkers($workers);
            $work->setNote(new Note($request->note));
            $this->repository->save($work);

            $this->updateRecipe($work, $recipe);
            $this->updateWorkerShifts($work);
            $this->updateSpendingForWork($work);
            $workersDto = [];
            $workers = $work->getWorkers();
            foreach ($workers as $worker) {
                $workersDto[] = new WorkerDto(
                    $worker->getId(),
                    $worker->getName()->getValue(),
                    $worker->getDailyRate()->getAmountAsFloat()
                );
            }
            $actualRecipe = $work->getRecipe();
            $recipeDto = $actualRecipe
                ? new RecipeDTO(
                    $actualRecipe->getId(),
                    $actualRecipe->getChemical()->getId(),
                    $actualRecipe->getChemical()->getCommercialName()->getValue(),
                    $actualRecipe->getChemical()->getActiveIngredient()->getValue(),
                    $actualRecipe->getProblem()?->getId(),
                    $actualRecipe->getProblem()?->getName()->getValue(),
                    $actualRecipe->getDosis()->getMl(),
                    $actualRecipe->getNote()
                )
                : null;
            return new EditWorkResponse(
                new WorkDto(
                    $work->getId(),
                    $work->getWorkType()->getId(),
                    $work->getWorkType()->getName()->getValue(),
                    $work->getPlantation()->getId(),
                    $work->getPlantation()->getName()->getValue(),
                    $work->getDate(),
                    $workersDto,
                    $work->getNote()->getValue(),
                    $recipeDto
                )
            );
        });
    }

    private function updateWorkerShifts(Work $work): void
    {
        $originalShifts = $work->getWorkerShifts();
        $workers = $work->getWorkers();
        $newWorkerIds = array_map(fn($w) => $w->getId(), $workers);

        foreach ($originalShifts as $shift) {
            $work->removeWorkerShift($shift);
            if (!in_array($shift->getWorker()->getId(), $newWorkerIds)) {
                $this->workerShiftRepository->delete($shift->getId());
            } else {
                $shift->setPlantation($work->getPlantation());
                $shift->setDate($work->getDate());
                $this->workerShiftRepository->save($shift);
                $work->addWorkerShift($shift);
            }
        }
        $shiftWorkerIds = array_map(fn($s) => $s->getWorker()->getId(), $work->getWorkerShifts());

        foreach ($workers as $worker) {
            if (!in_array($worker->getId(), $shiftWorkerIds)) {
                $shift = $this->workerShiftFactory->create(
                    $worker,
                    $work->getPlantation(),
                    $work->getDate(),
                    $worker->getDailyRate()
                );
                $work->addWorkerShift($shift);
                $this->workerShiftRepository->save($shift);
            }
        }
    }

    private function updateSpendingForWork(Work $work): void
    {
        $spendingGroup = $work->getSpendingGroup();
        $price = $work->getFullPrice();
        if ($price > 0) {
            if (!$spendingGroup) {
                $spendingGroup = $this->spendingGroupFactory->create(
                    SpendingType::WORK,
                    $work->getDate(),
                    new Money($work->getFullPrice()),
                    new Note(),
                    false
                );
                $spendingGroup->assignToWork($work);
                $this->spendingGroupRepository->save($spendingGroup);
                $spending = $this->spendingFactory->create(
                    $spendingGroup,
                    $work->getPlantation(),
                    new Money($price),
                );
                $this->spendingRepository->save($spending);
            } else {
                $spendingGroup->setDate($work->getDate());
                $spendingGroup->setAmount(new Money($price));
                $spending = $this->spendingRepository->getForGroup($spendingGroup->getId())[0];
                $spending->setAmount(new Money($price));
                $this->spendingRepository->save($spending);
            }
            $work->assignSpending($spendingGroup);
            $this->spendingGroupRepository->save($spendingGroup);
        } else {
            if ($spendingGroup) {
                $this->spendingRepository->deleteForGroup($spendingGroup->getId());
                $this->spendingGroupRepository->delete($spendingGroup->getId());
            }
        }
    }

    private function updateRecipe(Work $work, ?Recipe $newRecipe): void
    {
        $existingRecipe = $work->getRecipe();
        if (!$newRecipe) {
            if ($existingRecipe) {
                $this->recipeRepository->delete($existingRecipe->getId());
            }
            return;
        }

        if ($existingRecipe) {
            if ($existingRecipe->equals($newRecipe)) {
                return;
            }
            $existingRecipe->setChemical($newRecipe->getChemical());
            $existingRecipe->setProblem($newRecipe->getProblem());
            $existingRecipe->setDosis($newRecipe->getDosis());
            $existingRecipe->setNote($newRecipe->getNote());
            $this->recipeRepository->save($existingRecipe);
        } else {
            $newRecipe->assignWork($work);
            $this->recipeRepository->save($newRecipe);
            $work->setRecipe($newRecipe);
        }
    }
}
