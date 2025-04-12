<?php

namespace App\Tests;

use App\Domain\Entity\WorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetWorkTypeListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(
            WorkTypeRepositoryInterface::class
        );
        $this->truncateTables(['work_types']);
    }
    #[Test]
    public function testGetAllWorkTypesSuccess(): void
    {
        $work_typeNames = ['first WorkType', 'second WorkType'];
        foreach ($work_typeNames as $existingWorkType) {
            $this->repository->save(
                new WorkType(
                    new Name($existingWorkType)
                )
            );
        }
        $data = [
            'ids' => null,
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['work_types'] ?? null);
        $this->assertCount(2, $response['work_types']);
        $this->assertArrayHasKey('id', $response['work_types'][0]);
        $this->assertArrayHasKey('name', $response['work_types'][0]);
    }

    #[Test]
    public function testGetSomeWorkTypesSuccess(): void
    {
        $work_typeNames = ['first WorkType', 'second WorkType', 'third WorkType'];
        $ids = [];
        foreach ($work_typeNames as $existingWorkType) {
            $work_type = new WorkType(
                new Name($existingWorkType)
            );
            $this->repository->save($work_type);
            $ids[] = $work_type->getId();
        }
        $data = [
            'ids' => [$ids[0],$ids[2]],
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['work_types'] ?? null);
        $this->assertCount(2, $response['work_types']);
        $this->assertArrayHasKey('id', $response['work_types'][0]);
        $this->assertArrayHasKey('name', $response['work_types'][0]);
        $this->assertEquals('first WorkType', $response['work_types'][0]['name']);
        $this->assertEquals('third WorkType', $response['work_types'][1]['name']);
    }

    #[Test]
    public function testGetWorkTypeListWithInvalidKey(): void
    {
        $data = [
            'ids' => ['badKey'],
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(422);
    }
}
