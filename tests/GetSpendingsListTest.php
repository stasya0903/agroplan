<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Spending;
use App\Domain\Entity\Work;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkType;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use App\Domain\ValueObject\Money;

class GetSpendingsListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Plantation $plantation;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['work', 'spending', 'worker_shift', 'plantations']);
        $this->seed();
    }

    #[Test]
    public function testGetAllSpendingTypesSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/spending/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $response['spending']);
        $this->assertIsArray($response);
        $this->assertEquals(3107.33, $response['total']);
    }

    #[Test]
    public function testFilterByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => $this->plantation->getId() + 1
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $response['spending']);
        $this->assertEquals(0, $response['total']);
    }

    #[Test]
    public function testFilterBySpendingTypeSuccess(): void
    {
        $data = [
            "spendingTypeId" => SpendingType::GASOLINE->value
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['spending']);
        $this->assertEquals(957.33, $response['total']);
    }

    #[Test]
    public function testFilterByDateFromTypeSuccess(): void
    {
        $data = [
            "dateFrom" => '2025-04-25'
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $response['spending']);
        $this->assertEquals(2457.33, $response['total']);
    }

    #[Test]
    public function testFilterByDateToTypeSuccess(): void
    {
        $data = [
            "dateTo" => '2025-04-10'
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['spending']);
        $this->assertEquals(650, $response['total']);
    }

    public function seed(): void
    {
        $fertilization = SpendingType::FERTILIZER;
        $gas = SpendingType::GASOLINE;
        //create plantations
        $this->plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($this->plantation);


        // Create Works on different dates
        $this->createSpending('2025-04-10 00:00:00', $fertilization, $this->plantation, 650, 'new');
        $this->createSpending('2025-04-25 00:00:00', $fertilization, $this->plantation, 1500, 'new2');
        $this->createSpending('2025-06-01 00:00:00', $gas, $this->plantation, 957.33, 'new3');
    }

    private function createSpending(
        string $date,
        SpendingType $spendingType,
        Plantation $plantation,
        float $amount,
        string $note
    ): void {
        $work = new Spending(
            $plantation,
            $spendingType,
            new Date($date),
            Money::fromFloat($amount),
            new Note($note)
        );

        $this->repository->save($work);
    }
}
