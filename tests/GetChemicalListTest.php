<?php


use App\Domain\Entity\Chemical;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\ValueObject\Name;
use App\Infrastructure\Entity\ChemicalEntity;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetChemicalListTest extends WebTestCase
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
    public function testGetAllChemicalsSuccess(): void
    {
        $chemicalNames = ['first Chemical', 'second Chemical'];
        foreach ($chemicalNames as $existingChemical) {
            $this->repository->save(
                new Chemical(
                    new Name($existingChemical),
                    null
                ),
            );
        }
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/chemical/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertIsArray($response['chemicals'] ?? null);
        $this->assertCount(2, $response['chemicals']);
        $this->assertArrayHasKey('id', $response['chemicals'][0]);
        $this->assertArrayHasKey('commercialName', $response['chemicals'][0]);
    }
}
