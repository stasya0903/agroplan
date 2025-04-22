<?php

namespace App\Tests;

use App\Domain\Enums\IncomingTermType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\IncomingRepositoryInterface;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateIncomingTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(IncomingRepositoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['plantations']);
    }

    #[Test]
    public function testCreateIncomingSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => 25,
            "weight" => 100,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $response);
        $incoming = $this->repository->find($response['id']);

        $this->assertEquals($data['price'], $incoming->getPrice()->getAmountAsFloat());
        $this->assertEquals($data['note'], $incoming->getInfo()->getValue());
        $this->assertEquals($data['date'], $incoming->getDate()->getValue()->format('Y-m-d H:m:s'));
        $this->assertEquals($plantation->getId(), $incoming->getPlantation()->getId());
        $this->assertEquals(IncomingTermType::CONTADO, $incoming->getIncomingTerm());
        $this->assertEquals($data['weight'], $incoming->getWeight()->getKg());
        $this->assertEquals(2500, $incoming->getAmount()->getAmountAsFloat());
    }

    #[Test]
    public function testCreateIncomingWithNotExistingPlantation(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId() + 100,
            "date" => date('Y-m-d H:m:s'),
            "price" => 25,
            "weight" => 100,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
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
    public function testCreateIncomingWithZeroPrice(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => 0,
            "weight" => 100,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
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
    public function testCreateIncomingWithNegativePrice(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => -25,
            "weight" => 100,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
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

    public function testCreateIncomingWithZeroWeight(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => 100,
            "weight" => 0,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
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
    public function testCreateIncomingWithNegativeWeight(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        $data = [
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "price" => 25,
            "weight" => -100,
            "note" => "test incoming",
            "incomingTermId" => IncomingTermType::CONTADO,
            "buyerName" => 'new Buyer'
        ];
        $this->client->request(
            'POST',
            '/api/v1/incoming/add',
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
}
