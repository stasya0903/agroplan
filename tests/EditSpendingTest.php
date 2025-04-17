<?php

namespace App\Tests;

use App\Domain\Entity\Spending;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditSpendingTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->spendingRepository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->truncateTables(['work', 'worker_shift', 'spending', 'workers', 'plantations']);
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $this->existingSpending = new Spending(
            $plantation,
            SpendingType::FERTILIZER,
            new Date(date('Y-m-d h:i:s')),
            Money::fromFloat(888.3),
            new Note('new note')
        );
        $this->repository->save($this->existingSpending);
    }

    #[Test]
    public function testEditSpendingSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation 1'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::DIESEL->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test editing spending",
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        $this->assertEquals($this->existingSpending->getId(), $response['spending']['id']);
        $spending = $this->repository->find($this->existingSpending->getId());

        $this->assertEquals($data['amount'], $spending->getAmount()->getAmountAsFloat());
        $this->assertEquals($data['note'], $spending->getInfo()->getValue());
        $this->assertEquals($data['date'], $spending->getDate()->getValue()->format('Y-m-d H:m:s'));
        $this->assertEquals($plantation->getId(), $spending->getPlantation()->getId());
        $this->assertEquals(SpendingType::DIESEL, $spending->getType());
    }

    #[Test]
    public function testEditSpendingWithNotExistingPlantation(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationId" => 999,
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test work"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
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
    public function testEditSpendingForWorkType(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::WORK->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => 193.58,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Please edit work for Work spending type.', $content['message']);
    }
    #[Test]
    public function testEditSpendingWithZeroAmount(): void
    {

        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => 0,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
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
    public function testEditSpendingWithNegativeAmount(): void
    {

        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::FERTILIZER->value,
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => -999,
            "note" => "test spending"
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
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
    public function testEditSpendingForOtherWithEmptyNote(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "spendingTypeId" => SpendingType::OTHER->value,
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => 99.33,
            "note" => null
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
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
    public function testEditGhostSpending(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId() + 100,
            "spendingTypeId" => SpendingType::OTHER->value,
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "date" => date('Y-m-d H:m:s'),
            "amount" => 99.33,
            "note" => null
        ];
        $this->client->request(
            'POST',
            '/api/v1/spending/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Spending not found.', $content['message']);
    }
}
