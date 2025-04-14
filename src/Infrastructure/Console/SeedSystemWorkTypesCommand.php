<?php

namespace App\Infrastructure\Console;

use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Name;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed:system-work-types')]
class SeedSystemWorkTypesCommand extends Command
{
    public function __construct(
        private readonly WorkTypeRepositoryInterface $workTypeRepository
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            foreach (SystemWorkType::cases() as $type) {
                $existingWorkType = $this->workTypeRepository->find($type->value);
                if ($existingWorkType) {
                    $existingWorkType->rename(new Name($type->label()));
                    $this->workTypeRepository->save($existingWorkType);
                } else {
                    $newWorkType = new WorkType(new Name($type->label()));
                    $reflectionProperty = new \ReflectionProperty(WorkType::class, 'id');
                    $reflectionProperty->setAccessible(true);
                    $reflectionProperty->setValue($newWorkType, $type->value);
                    $this->workTypeRepository->save($newWorkType);
                }
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Error: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}
