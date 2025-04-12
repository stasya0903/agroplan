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

class GetPlantationListTest extends WebTestCase
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
    }
    #[Test]
    public function testGetAllPlantationsSuccess(): void
    {
        $plantationNames = ['first Plantation', 'second Plantation'];
        foreach ($plantationNames as $existingPlantation) {
            $this->repository->save(new Plantation(new PlantationName($existingPlantation)));
        }
        $data = [
            'ids' => null,
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['plantations'] ?? null);
        $this->assertCount(2, $response['plantations']);
        $this->assertArrayHasKey('id', $response['plantations'][0]);
        $this->assertArrayHasKey('name', $response['plantations'][0]);
    }

    #[Test]
    public function testGetSomePlantationsSuccess(): void
    {
        $plantationNames = ['first Plantation', 'second Plantation', 'third Plantation'];
        $ids = [];
        foreach ($plantationNames as $existingPlantation) {
            $plantation = new Plantation(new PlantationName($existingPlantation));
            $this->repository->save($plantation);
            $ids[] = $plantation->getId();
        }
        $data = [
            'ids' => [$ids[0],$ids[2]],
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['plantations'] ?? null);
        $this->assertCount(2, $response['plantations']);
        $this->assertArrayHasKey('id', $response['plantations'][0]);
        $this->assertArrayHasKey('name', $response['plantations'][0]);
        $this->assertEquals('first Plantation', $response['plantations'][0]['name']);
        $this->assertEquals('third Plantation', $response['plantations'][2]['name']);
    }

    #[Test]
    public function testGetPlantationListWithInvalidKey(): void
    {
        $data = [
            'ids' => ['badKey'],
        ];

        $this->client->request(
            'POST',
            '/api/v1/plantation/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(422);
    }
}
