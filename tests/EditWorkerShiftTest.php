<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\Worker;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Repository\WorkerShiftRepository;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditWorkerShiftTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Work $existingWork;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkRepositoryInterface::class);
        $this->workerRepo = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->workerShiftRepository = static::getContainer()->get(WorkerShiftRepository::class);
        $this->truncateTables(['work', 'spending', 'worker_shift', 'plantations']);
        $this->seed();
    }

    #[Test]
    public function testEditWorkerShiftSuccess(): void
    {
        $shifts = $this->existingWork->getWorkerShifts();
        $shift = $shifts[0];
        $data = [
            'workerShiftId' => $shift->getId(),
            'payment' => 100,
            'paid' => true
        ];
        $this->client->request(
            'POST',
            '/api/v1/worker_shift/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $workerShift = $this->workerShiftRepository->find($shift->getId());
        $this->assertEquals($workerShift->getPayment()->getAmountAsFloat(), $data['payment']);
        $this->assertTrue($workerShift->isPaid());
        $work = $this->repository->findWithAllData($shift->getWork()->getId());
        $shifts = $work->getWorkerShifts();
        $cost = array_reduce($shifts, function ($result, $item) {
            $result += $item->getPayment()->getAmount();
            return $result;
        }, 0);
        $this->assertEquals($work->getSpendingGroup()->getAmount()->getAmount(), $cost);
    }

    public function testEditNotExistingWorkerShift(): void
    {
        $shifts = $this->existingWork->getWorkerShifts();
        $shift = $shifts[0];
        $data = [
            'workerShiftId' => $shift->getId() + 100,
            'payment' => 100,
            'paid' => true
        ];
        $this->client->request(
            'POST',
            '/api/v1/worker_shift/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Worker Shift not found.', $content['message']);
    }

    public function seed(): void
    {
        //create plantations
        $plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($plantation);
        //create workers

        $worker1 = new Worker(new Name('Alice'), new Money(25000));
        $this->workerRepo->save($worker1);
        $this->createWork('2025-04-10 00:00:00', $plantation, [$worker1]); //4500
    }

    private function createWork(
        string $date,
        Plantation $plantation,
        array $workers
    ): void {
        $data = [
            "workTypeId" => SystemWorkType::HARVEST->value,
            "plantationId" => $plantation->getId(),
            "date" => $date,
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work"
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);
        $this->existingWork = $this->repository->findWithAllData($data['id']);
    }
}
