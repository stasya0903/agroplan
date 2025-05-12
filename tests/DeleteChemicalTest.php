<?php


use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class DeleteChemicalTest extends WebTestCase
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
    public function testDeleteChemicalSuccess(): void
    {
        $data = [
            'id' => $this->existingChemical->getId(),
        ];

        $this->client->request(
            'DELETE',
            '/api/v1/chemical/delete',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $chemical = $this->repository->find($this->existingChemical->getId());
        $this->assertNull($chemical);
    }
    #[Test]
    public function testDeleteNotExistingChemical(): void
    {
        $data = [
            'id' => 999,
        ];

        $this->client->request(
            'DELETE',
            '/api/v1/chemical/delete',
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
}
