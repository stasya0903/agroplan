<?php


use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class EditChemicalTest extends WebTestCase
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
        $this->existingChemical = new Chemical(
            new Name('initial Chemical'),
            null
        );
        $this->repository->save($this->existingChemical);
    }
    #[Test]
    public function testEditChemicalSuccess(): void
    {
        $data = [
            'id' => $this->existingChemical->getId(),
            "commercialName" => 'New Chemical',
            'activeIngredient' => 'activeIngredient'
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $chemical = $this->repository->find($this->existingChemical->getId());
        $this->assertEquals($data['commercialName'], $chemical->getCommercialName()->getValue());
        $this->assertEquals($data['activeIngredient'], $chemical->getActiveIngredient()->getValue());
    }

    #[Test]
    public function testEditChemicalWithEmptyName(): void
    {
        $data = [
            'id' => $this->existingChemical->getId(),
            "commercialName" => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/edit',
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
    public function testEditNotExistingChemical(): void
    {
        $data = [
            'id' => 999,
            "commercialName" => ''
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/edit',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Chemical not found.', $content['message']);
    }

    #[Test]
    public function testCreateChemicalWithDuplicateName(): void
    {
        $existingChemical = new Chemical(new Name('Existing Chemical'), null);
        $this->repository->save($existingChemical);

        // Send a POST request with a duplicate chemical name
        $data = [
            'id' => $this->existingChemical->getId(),
            "commercialName" => 'Existing Chemical'
        ];

        $this->client->request(
            'POST',
            '/api/v1/chemical/edit',
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
