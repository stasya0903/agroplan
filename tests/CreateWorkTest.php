<?php

namespace App\Tests;

use App\Domain\Entity\Chemical;
use App\Domain\Entity\ProblemType;
use App\Domain\Entity\Worker;
use App\Domain\Entity\WorkerShift;
use App\Domain\Entity\WorkType;
use App\Domain\Enums\SpendingType;
use App\Domain\Enums\SystemWorkType;
use App\Domain\Factory\PlantationFactoryInterface;
use App\Domain\Factory\WorkerFactoryInterface;
use App\Domain\Repository\ChemicalRepositoryInterface;
use App\Domain\Repository\PlantationRepositoryInterface;
use App\Domain\Repository\ProblemTypeRepositoryInterface;
use App\Domain\Repository\RecipeRepositoryInterface;
use App\Domain\Repository\SpendingGroupRepositoryInterface;
use App\Domain\Repository\SpendingRepositoryInterface;
use App\Domain\Repository\WorkerRepositoryInterface;
use App\Domain\Repository\WorkerShiftRepositoryInterface;
use App\Domain\Repository\WorkRepositoryInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\Name;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CreateWorkTest extends WebTestCase
{
    use TableResetTrait;

    private KernelBrowser $client;
    private mixed $repository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->repository = static::getContainer()->get(WorkRepositoryInterface::class);
        $this->plantationFactory = static::getContainer()->get(PlantationFactoryInterface::class);
        $this->plantationRepository = static::getContainer()->get(PlantationRepositoryInterface::class);
        $this->workerFactory = static::getContainer()->get(WorkerFactoryInterface::class);
        $this->workerRepository = static::getContainer()->get(WorkerRepositoryInterface::class);
        $this->workerShiftRepository = static::getContainer()->get(WorkerShiftRepositoryInterface::class);
        $this->spendingGroupRepository = static::getContainer()->get(SpendingGroupRepositoryInterface::class);
        $this->spendingRepository = static::getContainer()->get(SpendingRepositoryInterface::class);
        $this->recipeRepository = static::getContainer()->get(RecipeRepositoryInterface::class);
        $this->chemicalRepository = static::getContainer()->get(ChemicalRepositoryInterface::class);
        $this->problemTypeRepository = static::getContainer()->get(ProblemTypeRepositoryInterface::class);
        $this->truncateTables(['work', 'worker_shift', 'spending', 'workers', 'recipes', 'chemicals', 'problem_types']);
    }

    #[Test]
    public function testCreateWorkSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => SystemWorkType::HARVEST->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work"
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $data);
        $work = $this->repository->find($data['id']);
        $this->assertNotNull($work);

        $workerShifts = $this->workerShiftRepository->findByWork($data['id']);
        $this->assertCount(2, $workerShifts, 'There should be two worker shifts created.');
        foreach ($workerShifts as $shift) {
            /** @var WorkerShift $shift */
            $this->assertEquals($plantation->getId(), $shift->getPlantation()->getId());
            $this->assertContains(
                $shift->getWorker()->getId()->value,
                array_map(fn($w) => $w->getId()->value, $workers)
            );
            $this->assertEquals(350.00, $shift->getPayment()->getAmountAsFloat());
            $this->assertNotNull($shift->getWork());
            $this->assertEquals($work->getId(), $shift->getWork()->getId());
        }

        $spendingGroup = $this->spendingGroupRepository->findByWork($data['id']);
        $this->assertEquals(700.00, $spendingGroup->getAmount()->getAmountAsFloat());
        $this->assertEquals(SpendingType::WORK, $spendingGroup->getType());
        $this->assertEquals($work->getId(), $spendingGroup->getWork()?->getId());
        $spending = $this->spendingRepository->getForGroup($spendingGroup->getId());
        $this->assertCount(1, $spending, 'There should be one spending.');
        $this->assertEquals($spending[0]->getPlantation()->getId(), $plantation->getId());
    }

    #[Test]
    public function testCreateWorkWithNotExistingWorker(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);

        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => [1],
            "note" => "test work"
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Worker not found.', $content['message']);
    }

    #[Test]
    public function testCreateWorkWithNotExistingPlantation(): void
    {
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => 9999,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work"
        ];

        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Work type not found.', $content['message']);
    }

    #[Test]
    public function testCreateWorkWithRecipeSuccess(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        //create chemical
        $chemical = new Chemical(new Name('new chemical'), new Name('active ingredient'));
        $this->chemicalRepository->save($chemical);

        //create problem
        $problem =  new ProblemType(new Name('new problem'));
        $this->problemTypeRepository->save($problem);
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work",
            "recipe" => [
                "chemicalId" => $chemical->getId(),
                "dosis" => 250,
                "problemId" => $problem->getId(),
                "note" => "new"
            ]
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $response = $this->client->getResponse();
        $response = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('id', $response);
        $work = $this->repository->find($response['id']);
        $this->assertNotNull($work);

        $recipe = $this->recipeRepository->findByWork($response['id']);
        $this->assertEquals($data['recipe']['chemicalId'], $recipe->getChemical()->getId());
        $this->assertEquals($data['recipe']['problemId'], $recipe->getProblem()->getId());
    }
    #[Test]
    public function testCreateFumigationWorkWithNoRecipe(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers)
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Recipe is required for FERTILIZATION work.', $content['message']);
    }

    #[Test]
    public function testCreateNoFumigationWorkWithRecipe(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        //create chemical
        $chemical = new Chemical(new Name('new chemical'), new Name('active ingredient'));
        $this->chemicalRepository->save($chemical);

        //create problem
        $problem =  new ProblemType(new Name('new problem'));
        $this->problemTypeRepository->save($problem);
        $data = [
            "workTypeId" => SystemWorkType::HARVEST->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work",
            "recipe" => [
                "chemicalId" => $chemical->getId(),
                "dosis" => 250,
                "problemId" => $problem->getId(),
                "note" => "new"
            ]
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Recipe can be added only for FERTILIZATION work.', $content['message']);
    }
    #[Test]
    public function testCreateWorkWithRecipeNoChemical(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }

        //create problem
        $problem =  new ProblemType(new Name('new problem'));
        $this->problemTypeRepository->save($problem);
        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work",
            "recipe" => [
                "chemicalId" => 999,
                "dosis" => 250,
                "problemId" => $problem->getId(),
                "note" => "new"
            ]
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
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
    public function testCreateWorkWithRecipeNoProblemType(): void
    {
        //create plantation
        $plantation = $this->plantationFactory->create(new Name('new Plantation'));
        $this->plantationRepository->save($plantation);
        //create two workers
        $workers = [];
        $workersInfo = [
            ['name' => 'Worker1', 'dailyRate' => 350.00],
            ['name' => 'Worker2', 'dailyRate' => 350.00]
        ];
        foreach ($workersInfo as $info) {
            $worker = $this->workerFactory->create(
                new Name($info['name']),
                Money::fromFloat($info['dailyRate'])
            );
            $this->workerRepository->save($worker);
            $workers[] = $worker;
        }
        //create chemical
        $chemical = new Chemical(new Name('new chemical'), new Name('active ingredient'));
        $this->chemicalRepository->save($chemical);

        $data = [
            "workTypeId" => SystemWorkType::FERTILIZATION->value,
            "plantationId" => $plantation->getId(),
            "date" => date('Y-m-d H:m:s'),
            "workerIds" => array_map(fn($worker) => $worker->getId(), $workers),
            "note" => "test work",
            "recipe" => [
                "chemicalId" => $chemical->getId(),
                "dosis" => 250,
                "problemId" => 999,
                "note" => "new"
            ]
        ];
        $this->client->request(
            'POST',
            '/api/v1/work/add',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($data)
        );
        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
        $response = $this->client->getResponse();
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('message', $content);
        $this->assertEquals('Problem not found.', $content['message']);
    }
}
