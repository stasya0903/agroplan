<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\ValueObject\PlantationName;
use App\Infrastructure\Entity\PlantationEntity;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditPlantationTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(
            PlantationRepositoryInterface::class
        );
        $this->truncateTables(['plantations']);
        $this->existingPlantation = new Plantation(new PlantationName('initial Plantation'));
        $this->repository->save($this->existingPlantation);
    }
    #[Test]
    public function testEditPlantationSuccess(): void
    {
        $data = [
            'id' => $this->existingPlantation->getId(),
            'name' => 'New Plantation'
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $plantation = $this->repository->find($this->existingPlantation->getId());
        $this->assertEquals('New Plantation', $plantation->getName());
    }

    #[Test]
    public function testEditPlantationWithEmptyName(): void
    {
        $data = [
            'id' => $this->existingPlantation->getId(),
            'name' => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Name cannot be empty', $content['message']);
    }
    #[Test]
    public function testEditNotExistingPlantation(): void
    {
        $data = [
            'id' => 999,
            'name' => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/edit',
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
    public function testCreatePlantationWithDuplicateName(): void
    {
        $existingPlantation = new Plantation(new PlantationName('Existing Plantation'));
        $this->repository->save($existingPlantation);

        // Send a POST request with a duplicate plantation name
        $data = [
            'id' => $this->existingPlantation->getId(),
            'name' => 'Existing Plantation'
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Plantation name must be unique.', $content['message']);
    }
}
