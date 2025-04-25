<?php


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

class EditSpendingGroupTest extends WebTestCase
{
    use \App\Tests\TableResetTrait;

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
        $this->exsistingSpendingGroup = new SpendingGroup(
            SpendingType::FERTILIZER,
            new Date(date('Y-m-d h:i:s')),
            Money::fromFloat(888.3),
            new Note('new note')
        );
        $this->spendingGroupRepository->save($this->exsistingSpendingGroup);
        $this->existingSpending = new Spending(
            $this->exsistingSpendingGroup,
            $plantation,
            Money::fromFloat(588.3)
        );
        $this->repository->save($this->existingSpending);
        $this->secondExistingSpending = new Spending(
            $this->exsistingSpendingGroup,
            $plantation,
            Money::fromFloat(300)
        );

        $this->repository->save($this->secondExistingSpending);
    }

    #[Test]
    public function testEditSpendingGroupSuccess(): void
    {
        $data = $this->getData();
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        $this->assertEquals($this->exsistingSpendingGroup->getId(), $response['spendingGroup']['id']);
        $spendingGroup = $this->spendingGroupRepository->find($this->exsistingSpendingGroup->getId());

        $this->assertEquals($data['amount'], $spendingGroup->getAmount()->getAmountAsFloat());
        $this->assertEquals($data['spendingTypeId'], $spendingGroup->getType()->value);
        $this->assertEquals($data['note'], $spendingGroup->getInfo()->getValue());
        $this->assertEquals($data['date'], $spendingGroup->getDate()->getValue()->format('Y-m-d H:i:s'));

        $allSpending = $this->repository->getForGroup($spendingGroup->getId());
        $total = array_sum(array_map(fn($s) => $s->getAmount()->getAmount(), $allSpending));
        $this->assertEquals($spendingGroup->getAmount()->getAmount(), $total);
    }
    
    #[Test]
    public function testEditSpendingGroupForWorkType(): void
    {
        $this->exsistingSpendingGroup->setType(SpendingType::WORK);
        $this->spendingGroupRepository->save( $this->exsistingSpendingGroup);
       
        $data = $this->getData();
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
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
    public function testChangeToWorkTypeSpending(): void
    {
        $data = $this->getData();
        $data['spendingTypeId'] = SpendingType::WORK->value;
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
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
    public function testEditSpendingGroupWithZeroAmount(): void
    {

        $data = $this->getData();
        $data['amount'] = 0;

        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
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
    public function testEditSpendingGroupWithNegativeAmount(): void
    {

        $data = $this->getData();
        $data['amount'] = -1;
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
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
    public function testEditGhostSpendingGroup(): void
    {
        $data = $this->getData();
        $data['spendingGroupId'] = $this->exsistingSpendingGroup->getId() * 2;
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Spending group not found.', $content['message']);
    }
    public function testEditWithNoExistingGroupType(): void
    {
        $data = $this->getData();
        $data['spendingGroupId'] = count(SpendingType::cases()) * 2;
        $this->client->request(
            'POST',
            '/api/v1/spending_group/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Spending group not found.', $content['message']);
    }
    
    public function getData(){
        return [
            "spendingGroupId" => $this->exsistingSpendingGroup->getId(),
            "amount" => 500,
            "spendingTypeId" => SpendingType::DIESEL->value,
            "note" => "new note",
            "date" => '2025-05-16 20:45:22'
        ];
    }
}
