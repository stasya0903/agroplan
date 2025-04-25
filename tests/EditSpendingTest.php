<?php

namespace App\Tests;

use App\Domain\Entity\Spending;
use App\Domain\Entity\SpendingGroup;
use App\Domain\Enums\SpendingType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
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
        $this->spendingGroupRepository = static::getContainer()->get(SpendingGroupRepositoryInterface::class);
        $this->truncateTables(['work', 'worker_shift', 'spending', 'spending_group','workers', 'plantations']);
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $spendingGroup = new SpendingGroup(
            SpendingType::FERTILIZER,
            new Date(date('Y-m-d h:i:s')),
            Money::fromFloat(888.3),
            new Note('new note')
        );
        $this->spendingGroupRepository->save($spendingGroup);
        $this->existingSpending = new Spending(
            $spendingGroup,
            $plantation,
            Money::fromFloat(588.3)
        );
        $this->repository->save($this->existingSpending);
        $this->secondExistingSpending = new Spending(
            $spendingGroup,
            $plantation,
            Money::fromFloat(300)
        );

        $this->repository->save($this->secondExistingSpending);
    }

    #[Test]
    public function testEditSpendingSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation 1'));
        $this->plantationRepository->save($plantation);
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "plantationId" => $plantation->getId(),
            "amount" => 193.58,
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
        $this->assertEquals($plantation->getId(), $spending->getPlantation()->getId());

        $spendingGroup = $this->existingSpending->getSpendingGroup();
        $allSpending = $this->repository->getForGroup($spendingGroup->getId());
        $total = array_sum(array_map(fn($s) => $s->getAmount()->getAmount(), $allSpending));
        $this->assertEquals($spendingGroup->getAmount()->getAmount(), $total);
    }

    #[Test]
    public function testEditSpendingWithNotExistingPlantation(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "plantationId" => 999,
            "amount" => 193.58
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
        $spendingGroup = new SpendingGroup(
            SpendingType::WORK,
            new Date(date('Y-m-d h:i:s')),
            Money::fromFloat(888.3),
            new Note('new note')
        );
        $this->spendingGroupRepository->save($spendingGroup);
        $workSpending = new Spending(
            $spendingGroup,
            $plantation,
            Money::fromFloat(888.3)
        );
        $this->repository->save($workSpending);
        $data = [
            "spendingId" => $workSpending->getId(),
            "plantationId" => $plantation->getId(),
            "amount" => 193.58
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
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "amount" => 0
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
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "amount" => -999
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
    public function testEditGhostSpending(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId() + 100,
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "amount" => 99.33
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
    public function testEditWithBiggerThanGroupAmount(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "amount" => 900,
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
        $this->assertEquals('Amount should be less than group amount.', $content['message']);
    }
    public function testAssignAllAmountToOneSpending(): void
    {
        $data = [
            "spendingId" => $this->existingSpending->getId(),
            "plantationId" => $this->existingSpending->getPlantation()->getId(),
            "amount" => 888.3,
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
        $this->assertEquals('You cannot assign all amount to one spending.', $content['message']);
    }
    public function testAssignAmountForOnlyOneSpending(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $spendingGroup = new SpendingGroup(
            SpendingType::FERTILIZER,
            new Date(date('Y-m-d h:i:s')),
            Money::fromFloat(100),
            new Note('new note')
        );
        $this->spendingGroupRepository->save($spendingGroup);
        $existingSpending = new Spending(
            $spendingGroup,
            $plantation,
            Money::fromFloat(100)
        );
        $this->repository->save($existingSpending);
        $data = [
            "spendingId" => $existingSpending->getId(),
            "plantationId" => $existingSpending->getPlantation()->getId(),
            "amount" => 500,
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
        $this->assertEquals('Please change group amount for only spending in group.', $content['message']);
    }
}
