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

class GetSpendingGroupListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private array $plantations;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['work', 'spending_group', 'worker_shift', 'plantations']);
        $this->seed();
    }

    #[Test]
    public function testGetAllSpendingTypesSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/spending_group/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3, $response['spendingGroups']);
        $this->assertIsArray($response);
        $this->assertEquals(3107.33, $response['total']);
    }

    #[Test]
    public function testFilterByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => $this->plantations[0]->getId()
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending_group/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertCount(2, $response['spendingGroups']);
        $this->assertEquals(966.67, $response['total']);
    }

    #[Test]
    public function testFilterBySpendingTypeSuccess(): void
    {
        $data = [
            "spendingTypeId" => SpendingType::GASOLINE->value
        ];

        $this->client->request(
            'POST',
            '/api/v1/spending_group/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['spendingGroups']);
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
            '/api/v1/spending_group/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $response['spendingGroups']);
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
            '/api/v1/spending_group/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['spendingGroups']);
        $this->assertEquals(650, $response['total']);
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

        //216.6
        $this->createSpendingGroup(
            '2025-04-10 00:00:00',
            $fertilization,
            650,
            'new',
            array_map( fn(Plantation $plantation) => $plantation->getId(), $this->plantations)
        );
        //750
        $this->createSpendingGroup(
            '2025-04-25 00:00:00',
            $fertilization,
            1500,
            'new2',
            [$this->plantations[0]->getId(), $this->plantations[1]->getId()]
        );
        //478.6
        $this->createSpendingGroup(
            '2025-06-01 00:00:00',
            $gas,
            957.33,
            'new2',
            [$this->plantations[1]->getId(), $this->plantations[2]->getId()]
        );
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
