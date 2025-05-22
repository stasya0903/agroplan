<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Worker;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditWorkerTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;


    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->workerShiftRepository = static::getContainer()->get(WorkerShiftRepositoryInterface::class);
        $this->truncateTables(['workers']);
        $this->existingWorker = new Worker(
            new Name('initial worker'),
            Money::fromFloat(350.00)
        );
        $this->repository->save($this->existingWorker);
    }
    #[Test]
    public function testEditWorkerSuccess(): void
    {
        $this->createWork();
        $data = [
            'id' => $this->existingWorker->getId(),
            'name' => 'New Worker',
            'dailyRate' => 450.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $worker = $this->repository->find($data['id']);
        $this->assertNotNull($worker);
        $this->assertEquals($worker->getName()->getValue(), $data['name']);
        $this->assertEquals($worker->getDailyRate()->getAmountAsFloat(), $data['dailyRate']);
        $shift = $this->workerShiftRepository->getForWorker($worker->getId())[0];
        $this->assertEquals($data['dailyRate'], $shift->getPayment()->getAmountAsFloat());
    }

    #[Test]
    public function testEditWorkerWithEmptyName(): void
    {
        $data = [
            'id' => $this->existingWorker->getId(),
            'name' => '',
            'dailyRate' => 350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Name cannot be empty', $content['message']);
    }

    #[Test]
    public function testEditWorkerWithDuplicateName(): void
    {
        $existingWorker = new Worker(
            new Name('Existing Worker'),
            new Money(35000)
        );
        $this->repository->save($existingWorker);

        $data = [
            'id' => $this->existingWorker->getId(),
            'name' => 'Existing Worker',
            'dailyRate' => 350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Worker name must be unique.', $content['message']);
    }

    #[Test]
    public function testEditWorkerWithZeroRate(): void
    {
        $data = [
            'id' => $this->existingWorker->getId(),
            'name' => 'Unpaid worker',
            'dailyRate' => 0.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Amount cannot be empty', $content['message']);
    }

    public function testEditWorkerWithNegativeRate(): void
    {
        $data = [
            'id' => $this->existingWorker->getId(),
            'name' => 'Unpaid worker',
            'dailyRate' => -350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Amount must be greater than zero.', $content['message']);
    }
    #[Test]
    public function testEditNotExistingWorker(): void
    {
        $data = [
            'id' => 999,
            'name' => 'Goast worker',
            'dailyRate' => -350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
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

    private function createWork()
    {
        $plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "workTypeId" => SystemWorkType::HARVEST->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => [$this->existingWorker->getId()],
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
