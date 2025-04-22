<?php

namespace App\Tests;

use PHPUnit\Framework\Attributes\Test;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GetIncomingTermTypeListTest extends WebTestCase
{
    #[Test]
    public function testGetSpendingTypeList()
    {
        $client = static::createClient();
        $client->request('GET', '/api/v1/incoming_term_type/list');
        $this->assertResponseIsSuccessful();
        $this->assertJsonResponseStructure($client);
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true)['incomingTermTypes'];

        $this->assertContains(
            ['value' => 1, 'label' => 'Contado'],
            $data
        );
        $this->assertContains(
            ['value' => 4, 'label' => '22 días'],
            $data
        );
    }

    private function assertJsonResponseStructure($client): void
    {
        $response = $client->getResponse();
        $data = json_decode($response->getContent(), true)['incomingTermTypes'];

        foreach ($data as $item) {
            $this->assertArrayHasKey('value', $item);
            $this->assertArrayHasKey('label', $item);
        }
    }
}
