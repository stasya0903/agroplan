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
use App\Domain\Repository\SpendingGroupRepositoryInterface;
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

class CreateSpendingTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->groupRepository = static::getContainer()->get(SpendingGroupRepositoryInterface::class);
        $this->repository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->workerFactory = static::getContainer()->get(WorkerFactoryInterface::class);
        $this->workerRepository = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->workerShiftRepository = static::getContainer()->get(WorkerShiftRepositoryInterface::class);
        $this->spendingRepository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->truncateTables(['work', 'worker_shift', 'spending', 'workers', 'plantations']);
    }

    #[Test]
    public function testCreateSpendingSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $plantation2 = $this->plantationFactory->create(new Name('new Plantation2'));
        $this->plantationRepository->save($plantation);
        $this->plantationRepository->save($plantation2);
        $data = [
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationIds" => [$plantation->getId(), $plantation2->getId()],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test spending"
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
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('groupId', $response);
        $spendingGroup = $this->groupRepository->find($response['groupId']);

        $this->assertEquals($data['amount'], $spendingGroup->getAmount()->getAmountAsFloat());
        $this->assertEquals($data['note'], $spendingGroup->getInfo()->getValue());
        $this->assertEquals($data['date'], $spendingGroup->getDate()->getValue()->format('Y-m-d H:m:s'));
        $this->assertEquals(SpendingType::FERTILIZER, $spendingGroup->getType());

        $allSpending = $this->repository->getForGroup($spendingGroup->getId());
        $this->assertCount(2, $allSpending);
        $plantationIds = array_map(fn($s) => $s->getPlantation()->getId(), $allSpending);
        $this->assertContains($plantation->getId(), $plantationIds);
        $this->assertContains($plantation2->getId(), $plantationIds);

        $total = array_sum(array_map(fn($s) => $s->getAmount()->getAmount(), $allSpending));
        $this->assertEquals($spendingGroup->getAmount()->getAmount(), $total);


    }

    #[Test]
    public function testCreateSpendingWithNotExistingPlantation(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationIds" => [$plantation->getId() + 100],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test work"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
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
    #[Test]
    public function testCreateSpendingForWorkType(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingTypeId" => SpendingType::WORK->value,
            "plantationIds" => [$plantation->getId()],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Please create work for Work spending type.', $content['message']);
    }
    #[Test]
    public function testCreateSpendingWithZeroAmount(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationIds" => [$plantation->getId()],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 0,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
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
    #[Test]
    public function testCreateSpendingWithNegativeAmount(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationIds" => [$plantation->getId()],
            "date" => date('Y-m-d H:m:s'),
            "amount" => -999,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
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
    public function testCreateSpendingForOtherWithEmptyNote(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingTypeId" => SpendingType::OTHER->value,
            "plantationIds" => [$plantation->getId()],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 99.33,
            "note" => null
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Note is required for OTHER spending type.', $content['message']);
    }
    public function testCreateSpendingWithNoPlantations(): void
    {
        $data = [
            "spendingTypeId" => SpendingType::OTHER->value,
            "plantationIds" => [],
            "date" => date('Y-m-d H:m:s'),
            "amount" => 99.33,
            "note" => null
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Please chose at list one plantation for spending.', $content['message']);
    }
}
