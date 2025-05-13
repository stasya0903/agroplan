<?php

namespace App\Tests;

use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateChemicalTest extends WebTestCase
{
    use \App\Tests\TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(
            ChemicalRepositoryInterface::class
        );
        $this->truncateTables(['chemicals']);
    }

    #[Test]
    public function testCreateChemicalSuccess(): void
    {
        $data = [
            'commercialName' => 'New Chemical',
            'activeIngredient' => 'active ingredient',
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $chemical = $this->repository->existsByName('New Chemical');
        $this->assertNotNull($chemical);
    }

    #[Test]
    public function testCreateChemicalWithEmptyName(): void
    {
        $data = [
            'commercialName' => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/add',
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
    public function testCreateChemicalWithDuplicateName(): void
    {
        $existingChemical = new Chemical(
            new Name('Existing Chemical'),
            null
        );
        $this->repository->save($existingChemical);

        // Send a POST request with a duplicate chemical name
        $data = [
            'commercialName' => 'Existing Chemical'
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Chemical name must be unique.', $content['message']);
    }
}
