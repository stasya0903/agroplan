<?php

namespace App\Tests;

use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkerShift;
use App\Domain\Entity\WorkType;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateWorkTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkRepositoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->workerFactory = static::getContainer()->get(WorkerFactoryInterface::class);
        $this->workerRepository = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->workerShiftRepository = static::getContainer()->get(WorkerShiftRepositoryInterface::class);
        $this->spendingRepository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->truncateTables(['work', 'worker_shift', 'spending']);
    }

    #[Test]
    public function testCreateWorkSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId()->value, $workers),
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

        $this->assertArrayHasKey('id', $data);
        $work = $this->repository->find($data['id']);
        $this->assertNotNull($work);

        $workerShifts = $this->workerShiftRepository->findByWork($data['id']);
        $this->assertCount(2, $workerShifts, 'There should be two worker shifts created.');
        foreach ($workerShifts as $shift) {
            /** @var WorkerShift $shift */
            $this->assertEquals($plantation->getId(), $shift->getPlantation()->getId());
            $this->assertContains($shift->getWorker()->getId()->value, array_map(fn($w) => $w->getId()->value, $workers));
            $this->assertEquals(350.00, $shift->getPayment()->getAmountAsFloat());
            $this->assertNotNull($shift->getWork());
            $this->assertEquals($work->getId(), $shift->getWork()->getId());
        }

        $spending = $this->spendingRepository->findByWork($data['id']);
        $this->assertEquals(700.00, $spending->getAmount()->getAmountAsFloat());
        $this->assertEquals($plantation->getId(), $spending->getPlantation()->getId());
        $this->assertEquals(SpendingType::WORK, $spending->getType());
        $this->assertEquals($work->getId(), $spending->getWork()?->getId());

    }

    #[Test]
    public function testCreateWorkWithNotExistingWorker(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);

        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => [1],
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
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Worker not found.', $content['message']);
    }

    public function testCreateWorkWithNotExistingPlantation(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => 90,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId()->value, $workers),
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
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Work type not found.', $content['message']);
    }

    public function testCreateWorkWithNotWorkType(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId()->value, $workers),
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
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Plantation not found.', $content['message']);
    }
}
