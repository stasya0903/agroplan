<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetSpendingTypeListTest extends WebTestCase
{
    #[Test]
    public function testGetSpendingTypeList()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/spending_type/list');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponseStructure($client);
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true)['spendingTypes'];

        $this->assertContains(
            ['value' => 2, 'label' => 'Diesel'],
            $data
        );
        $this->assertContains(
            ['value' => 7, 'label' => 'Fertilizante'],
            $data
        );
    }

    private function assertJsonResponseStructure($client): void
    {
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true)['spendingTypes'];

        foreach ($data as $item) {
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }
}
