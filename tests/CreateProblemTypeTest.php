<?php

namespace App\Tests;

use App\Domain\Entity\ProblemType;
use App\Domain\Repository\ProblemTypeRepositoryInterface;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateProblemTypeTest extends WebTestCase
{
    use \App\Tests\TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(
            ProblemTypeRepositoryInterface::class
        );
        $this->truncateTables(['problem_types']);
    }

    #[Test]
    public function testCreateProblemTypeSuccess(): void
    {
        $data = [
            'name' => 'New ProblemType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/problem_type/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $problemType = $this->repository->existsByName('New ProblemType');
        $this->assertNotNull($problemType);
    }

    #[Test]
    public function testCreateProblemTypeWithEmptyName(): void
    {
        $data = [
            'name' => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/problem_type/add',
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
    public function testCreateProblemTypeWithDuplicateName(): void
    {
        $existingProblemType = new ProblemType(new Name('Existing ProblemType'));
        $this->repository->save($existingProblemType);

        // Send a POST request with a duplicate problemType name
        $data = [
            'name' => 'Existing ProblemType'
        ];

        $this->client->request(
            'POST',
            '/api/v1/problem_type/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('ProblemType name must be unique.', $content['message']);
    }
}
