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

class CreateWorkTypeTest extends WebTestCase
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
    public function testCreateWorkTypeSuccess(): void
    {
        $data = [
            'name' => 'New WorkType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $workType = $this->repository->existsByName($data['name']);
        $this->assertNotNull($workType);
    }

    #[Test]
    public function testCreateWorkTypeWithEmptyName(): void
    {
        $data = [
            'name' => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/add',
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
    public function testCreateWorkTypeWithDuplicateName(): void
    {
        $existingWorkType = new WorkType(
            new Name('Existing WorkType')
        );
        $this->repository->save($existingWorkType);

        $data = [
            'name' => 'Existing WorkType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/add',
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

    public function testCreateWorkTypeWithSystemName(): void
    {
        $data = [
            'name' => SystemWorkType::OTHER->label()
        ];

        $this->client->request(
            'POST',
            '/api/v1/work_type/add',
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
    private function runSeederCommand(): void
    {
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);

        $input = new ArrayInput([
            'command' => 'app:seed:system-work-types --env=test',
        ]);
        $output = new BufferedOutput();

        $exitCode = $application->run($input, $output);
        if ($exitCode !== 0) {
            throw new \RuntimeException('Seeder command failed: ' . $output->fetch());
        }
    }
}
