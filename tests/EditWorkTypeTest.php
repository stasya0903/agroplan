<?php

namespace App\Tests;

use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Seeder\SystemWorkTypeSeeder;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\Response;

class EditWorkTypeTest extends WebTestCase
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
        $this->existingWorkType = new WorkType(
            new Name('initial work_type')
        );
        $this->repository->save($this->existingWorkType);
    }
    #[Test]
    public function testEditWorkTypeSuccess(): void
    {
        $data = [
            'id' => $this->existingWorkType->getId(),
            'name' => 'Edited WorkType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $workType = $this->repository->find($data['id']);
        $this->assertNotNull($workType);
        $this->assertEquals($workType->getName()->getValue(), $data['name']);
    }

    #[Test]
    public function testEditWorkTypeWithEmptyName(): void
    {
        $data = [
            'id' => $this->existingWorkType->getId(),
            'name' => '',
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
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
    public function testEditWorkTypeWithDuplicateName(): void
    {
        $existingWorkType = new WorkType(
            new Name('Existing WorkType')
        );
        $this->repository->save($existingWorkType);

        $data = [
            'id' => $this->existingWorkType->getId(),
            'name' => 'Existing WorkType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('WorkType name must be unique.', $content['message']);
    }

    public function testEditNotExistingWorkType(): void
    {
        $data = [
            'id' => 999,
            'name' => 'Goast work_type'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('WorkType not found.', $content['message']);
    }
    public function testEditWorkTypeWithSystemName(): void
    {
        $data = [
            'id' => $this->existingWorkType->getId(),
            'name' => SystemWorkType::OTHER->label()
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('WorkType name used by system.', $content['message']);
    }

    public function testEditSystemWorkType(): void
    {
        $data = [
            'id' => SystemWorkType::OTHER->value,
            'name' => 'System work_type'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Can not edit system work type', $content['message']);
    }
}
