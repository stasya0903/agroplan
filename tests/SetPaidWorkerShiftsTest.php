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

class SetPaidWorkerShiftsTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Plantation $plantation;
    private Worker $worker1;
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
    public function testSetPaidWorkerShiftsSuccess(): void
    {
        $shifts = $this->existingWork->getWorkerShifts();
        $shift = $shifts[0];
        $data = [
            'workerShiftIds' => [$shift->getId()],
        ];
        $this->client->request(
            'POST',
            '/api/v1/worker_shift/set_paid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $workerShift = $this->workerShiftRepository->find($shift->getId());
        $this->assertTrue($workerShift->isPaid());
    }

    public function testSetPaidEmptyWorkerShifts(): void
    {
        $data = [
            'workerShiftIds' => [],
        ];
        $this->client->request(
            'POST',
            '/api/v1/worker_shift/set_paid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Chose worker shifts to be paid', $content['message']);
    }

    public function testSetPaidWorkerShiftsWithWrongId(): void
    {
        $data = [
            'workerShiftIds' => ['bad argument'],
        ];
        $this->client->request(
            'POST',
            '/api/v1/worker_shift/set_paid',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals("All worker shift IDs must be integers.", $content['message']);
    }

    public function seed(): void
    {
        //create plantations
        $this->plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($this->plantation);
        //create workers

        $this->worker1 = new Worker(new Name('Alice'), new Money(25000));
        $this->workerRepo->save($this->worker1);
        $this->createWork('2025-04-10 00:00:00', $this->plantation, [$this->worker1]);
    }

    private function createWork(
        string $date,
        Plantation $plantation,
        array $workers
    ): void {
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
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
        $this->existingWork = $this->repository->findWithShiftsAndSpending($data['id']);
    }
}
