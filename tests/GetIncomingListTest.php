<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Incoming;
use App\Domain\Entity\Incominger;
use App\Domain\Entity\IncomingType;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Enums\SystemIncomingType;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingerRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\Repository\IncomingTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetIncomingListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Plantation $plantation;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(IncomingRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->factory = static::getContainer()->get(IncomingFactoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->truncateTables(['incoming', 'plantations']);
        $this->seed();
    }

    #[Test]
    public function testGetAllIncomingTypesSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/incoming/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['incoming']);
        $this->assertCount(3, $response['incoming']);
        $this->assertEquals(18000, $response['total']);
    }

    #[Test]
    public function testFilterByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => 100
        ];

        $this->client->request(
            'POST',
            '/api/v1/incoming/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $response['incoming']);
    }
    #[Test]
    public function testFilterByDateFromTypeSuccess(): void
    {
        $data = [
            "dateFrom" => '2025-04-25'
        ];

        $this->client->request(
            'POST',
            '/api/v1/incoming/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $response['incoming']);
        $this->assertEquals(12000, $response['total']);
    }
    #[Test]
    public function testFilterByDateToTypeSuccess(): void
    {
        $data = [
            "dateTo" => '2025-04-10'
        ];

        $this->client->request(
            'POST',
            '/api/v1/incoming/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response['incoming']);
        $this->assertEquals(6000, $response['total']);
    }

    public function seed(): void
    {
        $this->createIncoming('2025-04-10 00:00:00');
        $this->createIncoming('2025-04-25 00:00:00');
        $this->createIncoming('2025-06-01 00:00:00');
    }

    private function createIncoming(
        string $date
        
    ): void {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
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
}
