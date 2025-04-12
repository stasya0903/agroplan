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

class CreateWorkerTest extends WebTestCase
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
    public function testCreateWorkerSuccess(): void
    {
        $data = [
            'name' => 'New Worker',
            'dailyRate' => 350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $worker = $this->repository->existsByName($data['name']);
        $this->assertNotNull($worker);
    }

    #[Test]
    public function testCreateWorkerWithEmptyName(): void
    {
        $data = [
            'name' => '',
            'dailyRate' => 350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/add',
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
    public function testCreateWorkerWithDuplicateName(): void
    {
        $existingWorker = new Worker(
            new WorkerName('Existing Worker'),
            new Money(35000)
        );
        $this->repository->save($existingWorker);

        $data = [
            'name' => 'Existing Worker',
            'dailyRate' => '350.00'
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/add',
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

    public function testCreateWorkerWithZeroRate(): void
    {
        $data = [
            'name' => 'Unpaid worker',
            'dailyRate' => '0.00'
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/add',
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

    public function testCreateWorkerWithNegativeRate(): void
    {
        $data = [
            'name' => 'Unpaid worker',
            'dailyRate' => -350.00
        ];

        $this->client->request(
            'POST',
            '/api/v1/worker/add',
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
}
