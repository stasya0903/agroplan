<?php

namespace App\Tests;

use App\Domain\Entity\Plantation;
use App\Domain\Entity\Work;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\Repository\WorkTypeRepositoryInterface;
use App\Domain\ValueObject\Date;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use App\Domain\ValueObject\Note;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GetWorkListTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;
    private Plantation $plantation;

    protected function setUp(): void
    {

        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkRepositoryInterface::class);
        $this->workTypeRepository = static::getContainer()->get(WorkTypeRepositoryInterface::class);
        $this->workerRepo = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->truncateTables(['work', 'spending', 'worker_shift', 'plantations']);
        $this->seed();
    }
    #[Test]
    public function testGetAllWorkTypesSuccess(): void
    {
        $data = [];

        $this->client->request(
            'POST',
            '/api/v1/work/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(3,$response);
        $this->assertIsArray($response);
        $this->assertArrayHasKey('id', $response[0]);
    }

    #[Test]
    public function testFilterByPlantationSuccess(): void
    {
        $data = [
            "plantationId" => $this->plantation->getId() + 1
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(0, $response );
    }

    public function testFilterByWorkTypeSuccess(): void
    {
        $data = [
            "workTypeId" => SystemWorkType::HARVEST->value
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response );
    }

    public function testFilterByDateFromTypeSuccess(): void
    {
        $data = [
            "dateFrom" => '2025-04-25'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(2, $response );
    }

    public function testFilterByDateToTypeSuccess(): void
    {
        $data = [
            "dateTo" => '2025-04-10'
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/list',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount(1, $response );
    }
    public function seed(): void
    {
        $fertilization = $this->workTypeRepository->find(SystemWorkType::FERTILIZATION->value);
        $harvest = $this->workTypeRepository->find(SystemWorkType::HARVEST->value);
        //create plantations
        $this->plantation = new Plantation(new Name('Plantation'));
        $this->plantationRepository->save($this->plantation);


        // Create Works on different dates
        $this->createWork('2025-04-10 00:00:00', $fertilization, $this->plantation, []); // Should be included
        $this->createWork('2025-04-25 00:00:00', $fertilization, $this->plantation, []); // Should be included
        $this->createWork('2025-06-01 00:00:00', $harvest, $this->plantation, []); // Should NOT be included
    }

    private function createWork(
        string $date,
        WorkType $workType,
        Plantation $plantation,
        array $workers
    ): void {
        $work = new Work(
            $workType,
            $plantation,
            new Date($date),
            $workers,
            new Note('test')
        );

        $this->repository->save($work);
    }
}
