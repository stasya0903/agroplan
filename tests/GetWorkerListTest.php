<?php

namespace App\Tests;

use App\Domain\Entity\Worker;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\WorkerName;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetWorkerListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(
            WorkerRepositoryInterface::class
        );
        $this->truncateTables(['workers']);
    }
    #[Test]
    public function testGetAllWorkersSuccess(): void
    {
        $workerNames = ['first Worker', 'second Worker'];
        foreach ($workerNames as $existingWorker) {
            $this->repository->save(
                new Worker(
                    new WorkerName($existingWorker),
                    Money::fromFloat(350.00)
                )
            );
        }
        $data = [
            'ids' => null,
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['workers'] ?? null);
        $this->assertCount(2, $response['workers']);
        $this->assertArrayHasKey('id', $response['workers'][0]);
        $this->assertArrayHasKey('name', $response['workers'][0]);
    }

    #[Test]
    public function testGetSomeWorkersSuccess(): void
    {
        $workerNames = ['first Worker', 'second Worker', 'third Worker'];
        $ids = [];
        foreach ($workerNames as $existingWorker) {
            $worker = new Worker(
                new WorkerName($existingWorker),
                Money::fromFloat(350.00)
            );
            $this->repository->save($worker);
            $ids[] = $worker->getId();
        }
        $data = [
            'ids' => [$ids[0],$ids[2]],
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['workers'] ?? null);
        $this->assertCount(2, $response['workers']);
        $this->assertArrayHasKey('id', $response['workers'][0]);
        $this->assertArrayHasKey('name', $response['workers'][0]);
        $this->assertEquals('first Worker', $response['workers'][0]['name']);
        $this->assertEquals('third Worker', $response['workers'][1]['name']);
    }

    #[Test]
    public function testGetWorkerListWithInvalidKey(): void
    {
        $data = [
            'ids' => ['badKey'],
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(422);
    }
}
