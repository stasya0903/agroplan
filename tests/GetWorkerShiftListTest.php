<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetWorkerShiftListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Plantation $plantation;
    private Worker $worker1;
    private Worker $worker2;

    protected function setUp(): void
    {

        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkRepositoryInterface::class);
        $this->workTypeRepository = static::getContainer()->get(WorkTypeRepositoryInterface::class);
        $this->workerRepo = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['work', 'spending', 'worker_shift', 'plantations']);
        $this->seed();
    }
    #[Test]
    public function testGetAllWorkTypesSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(4,$response["workerShifts"]);
        $this->assertIsArray($response["workerShifts"]);
        $this->assertArrayHasKey('id', $response["workerShifts"][0]);
        $this->assertEquals(900.00, $response["totalToPay"]);
    }

    #[Test]
    public function testFilterByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => $this->plantation->getId() + 1
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0,$response["workerShifts"]);
        $this->assertIsArray($response["workerShifts"]);
        $this->assertEquals(0, $response["totalToPay"]);
    }

    public function testFilterByWorkerSuccess(): void
    {
        $data = [
            "workerId" => $this->worker1->getId()
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2,$response["workerShifts"]);
        $this->assertIsArray($response["workerShifts"]);
        $this->assertEquals($this->worker1->getId(), $response["workerShifts"][0]['workerId']);
        $this->assertEquals($this->worker1->getId(), $response["workerShifts"][1]['workerId']);
        $this->assertEquals(500.00, $response["totalToPay"]);
    }

    public function testFilterByDateFromTypeSuccess(): void
    {
        $data = [
            "dateFrom" => '2025-04-25'
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2,$response["workerShifts"]);
        $this->assertEquals(450.00, $response["totalToPay"]);
    }

    public function testFilterByDateToTypeSuccess(): void
    {
        $data = [
            "dateTo" => '2025-04-10'
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2,$response["workerShifts"]);
        $this->assertEquals(450.00, $response["totalToPay"]);
    }
    public function testFilterByPaidToTypeSuccess(): void
    {
        $data = [
            "paid" => true
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker_shift/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $response["workerShifts"]);
        $this->assertEquals(0, $response["totalToPay"]);
    }
    public function seed(): void
    {
        //create plantations
        $this->plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($this->plantation);
        //create workers

        $this->worker1 = new Worker(new Name('Alice'), new Money(25000));
        $this->worker2 = new Worker(new Name('Bob'), new Money(20000));
        $this->workerRepo->save($this->worker1);
        $this->workerRepo->save($this->worker2);

        // Create Works on different dates
        $this->createWork('2025-04-10 00:00:00', $this->plantation, [$this->worker1, $this->worker2]); //4500
        $this->createWork('2025-04-25 00:00:00', $this->plantation, [$this->worker1]); // 2500
        $this->createWork('2025-06-01 00:00:00', $this->plantation, [$this->worker2]); // 2000
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
    }
}
