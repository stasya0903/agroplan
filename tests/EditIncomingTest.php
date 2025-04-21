<?php

namespace App\Tests;

use App\Domain\Entity\Incoming;
use App\Domain\Enums\IncomingTermType;
use App\Domain\Factory\IncomingFactoryInterface;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use App\Domain\ValueObject\Weight;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditIncomingTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private $incoming;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(IncomingRepositoryInterface::class);
        $this->factory = static::getContainer()->get(IncomingFactoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['plantations']);
        $this->seed();
    }

    #[Test]
    public function testEditIncomingSuccess(): void
    {
        $data = $this->getRequestData();
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true)['incoming'];

        $this->assertArrayHasKey('incomingId', $response);
        $incoming = $this->repository->find($response['id']);

        $this->assertEquals($data['price'], $incoming->getPrice()->getAmountAsFloat());
        $this->assertEquals($data['note'], $incoming->getInfo()->getValue());
        $this->assertEquals($data['date'], $incoming->getDate()->getValue()->format('Y-m-d H:m:s'));
        $this->assertEquals($data['plantationId'], $incoming->getPlantation()->getId());
        $this->assertEquals(IncomingTermType::CONTADO, $incoming->getIncomingTerm());
        $this->assertEquals($data['weight'], $incoming->getWeight()->getKg());
        $this->assertEquals(1000, $incoming->getAmount()->getAmountAsFloat());
    }

    #[Test]
    public function testEditIncomingWithNotExistingPlantation(): void
    {
        $data = $this->getRequestData();
        $data['plantationId'] = 100;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
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
    public function testEditIncomingWithZeroPrice(): void
    {
        $data = $this->getRequestData();
        $data['price'] = 0;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
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
    public function testEditIncomingWithNegativePrice(): void
    {
        $data = $this->getRequestData();
        $data['price'] = -25;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
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

    public function testEditIncomingWithZeroWeight(): void
    {
        $data = $this->getRequestData();
        $data['weight'] = 0;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals("Weight must be positive.", $content['message']);
    }
    #[Test]
    public function testEditIncomingWithNegativeWeight(): void
    {
        $data = $this->getRequestData();
        $data['weight'] = -22;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals("Weight must be positive.", $content['message']);
    }
    public function testEditPayIncomingWithNoPaidDate(): void
    {
        $data = $this->getRequestData();
        $data['datePaid'] = null;
        $this->client->request(
            'POST',
            '/api/v1/incoming/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('To pay incoming please add paid date.', $content['message']);
    }
    private function seed(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $this->incoming = $this->factory->create(
            $plantation,
            new Date(date('Y-m-d H:i:s')),
            Money::fromFloat(6000),
            new Note('note'),
            new Weight(10),
            IncomingTermType::CONTADO,
            new Name('name'),
            Money::fromFloat(60)
        );
        $this->repository->save($this->incoming);
    }
    private function getRequestData(): array
    {
        return [
            "incomingId" => $this->incoming->getId(),
            "plantationId" => $this->incoming->getPlantation()->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => 100,
            "weight" => 10,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer',
            "paid" => true,
            "datePaid" => date('Y-m-d H:m:s'),
        ];
    }
}
