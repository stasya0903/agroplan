<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Incoming;
use App\Domain\Entity\Incominger;
use App\Domain\Entity\IncomingType;
use App\Domain\Entity\Spending;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemIncomingType;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingerRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\Repository\IncomingTypeRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetBudgetListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private array $plantations;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(IncomingRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->factory = static::getContainer()->get(IncomingFactoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->spendingRepository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->truncateTables(['incoming', 'plantations', 'work', 'spending', 'worker_shift']);
        $this->seed();
    }

    #[Test]
    public function testGetBudgetForAllSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/budget/get',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['incoming']);
        $this->assertCount(3, $response['incoming']);
        $this->assertEquals(18000, $response['totalIncome']);
        $this->assertIsArray($response['spending']);
        $this->assertCount(7, $response['spending']);
        $this->assertEquals(3107.33, $response['totalSpend']);
        $this->assertEquals(14892.67, $response['profit']);
    }

    #[Test]
    public function testFilterBudgetByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => $this->plantations[1]->getId()
        ];

        $this->client->request(
            'POST',
            '/api/v1/budget/get',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['incoming']);
        $this->assertCount(1, $response['incoming']);
        $this->assertEquals(6000, $response['totalIncome']);
        $this->assertIsArray($response['spending']);
        $this->assertCount(3, $response['spending']);
        $this->assertEquals(1445.34, $response['totalSpend']);
        $this->assertEquals(4554.66, $response['profit']);
    }

    #[Test]
    public function testFilterBudgetByDateFromTypeSuccess(): void
    {
        $data = [
            "dateFrom" => '2025-04-25'
        ];

        $this->client->request(
            'POST',
            '/api/v1/budget/get',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $response['incoming']);
        $this->assertEquals(12000, $response['totalIncome']);
        $this->assertIsArray($response['spending']);
        $this->assertCount(4, $response['spending']);
        $this->assertEquals(2457.33, $response['totalSpend']);
        $this->assertEquals(9542.67, $response['profit']);
    }

    #[Test]
    public function testFilterBudgetByDateToTypeSuccess(): void
    {
        $data = [
            "dateTo" => '2025-04-10'
        ];

        $this->client->request(
            'POST',
            '/api/v1/budget/get',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['incoming']);
        $this->assertEquals(6000, $response['totalIncome']);
        $this->assertCount(3, $response['spending']);
        $this->assertEquals(650, $response['totalSpend']);
        $this->assertEquals(5350, $response['profit']);
    }

    public function seed(): void
    {
        $fertilization = SpendingType::FERTILIZER;
        $gas = SpendingType::GASOLINE;

        //create plantations
        $plantations = ['Plantation One', 'Plantation Two', 'Plantation Three'];
        foreach ($plantations as $plantation) {
            $plantation = new Plantation(new Name($plantation));
            $this->plantationRepository->save($plantation);
            $this->plantations[] = $plantation;
        }

        $this->createSpendingGroup(
            '2025-04-10 00:00:00',
            $fertilization,
            650,
            'new',
            array_map( fn(Plantation $plantation) => $plantation->getId(), $this->plantations)
        );
        $this->createSpendingGroup(
            '2025-04-25 00:00:00',
            $fertilization,
            1500,
            'new2',
            [$this->plantations[0]->getId(), $this->plantations[1]->getId()]
        );
        $this->createSpendingGroup(
            '2025-06-01 00:00:00',
            $gas,
            957.33,
            'new2',
            [$this->plantations[1]->getId(), $this->plantations[2]->getId()]
        );
        $this->createIncoming('2025-04-10 00:00:00',$this->plantations[0]); // 6000
        $this->createIncoming('2025-04-25 00:00:00',$this->plantations[1]); // 6000
        $this->createIncoming('2025-06-01 00:00:00',$this->plantations[2]); // 6000
    }

    private function createIncoming(
        string $date,
        Plantation $plantation,
    ): void {
        $incoming = new Incoming(
            $plantation,
            new Date($date),
            Money::fromFloat(6000),
            new Note('note'),
            new Weight(10),
            IncomingTermType::CONTADO,
            new Name('name'),
            Money::fromFloat(60)
        );
        $this->repository->save($incoming);
    }
    private function createSpendingGroup(
        string $date,
        SpendingType
        $spendingType,
        float $amount,
        string $note,
        array $plantationIds
    ): void {
        $data = [
            "spendingTypeId" => $spendingType->value,
            "plantationIds" => $plantationIds,
            "date" => $date,
            "amount" => $amount,
            "note" => $note
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
