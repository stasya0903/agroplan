<?php

namespace App\Tests;

use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
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
    }
    #[Test]
    public function testGetAllWorkTypesSuccess(): void
    {
        $work_typeNames = ['first WorkType', 'second WorkType'];
        foreach ($work_typeNames as $existingWorkType) {
            $this->repository->save(new WorkType(new Name($existingWorkType)));
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

        $this->assertIsArray($response['workTypes'] ?? null);
        $systemTypes = SystemWorkType::cases();
        $this->assertCount(2 + count($systemTypes), $response['workTypes']);
        $this->assertArrayHasKey('id', $response['workTypes'][0]);
        $this->assertArrayHasKey('name', $response['workTypes'][0]);
        $data = json_decode($response->getContent(), true);
        $this->assertContains(
            ['value' => 3, 'label' => 'fumigada'],
            $data
        );
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

        $this->assertIsArray($response['workTypes'] ?? null);
        $this->assertCount(2, $response['workTypes']);
        $this->assertArrayHasKey('id', $response['workTypes'][0]);
        $this->assertArrayHasKey('name', $response['workTypes'][0]);
        $this->assertEquals('first WorkType', $response['workTypes'][0]['name']);
        $this->assertEquals('third WorkType', $response['workTypes'][1]['name']);
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
