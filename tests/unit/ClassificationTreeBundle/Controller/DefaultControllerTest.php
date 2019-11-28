<?php

namespace Tests\Unit\Divante\ScheduledExportBundle\Controller;

use Pimcore\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class DefaultControllerTest extends WebTestCase
{
    /** @var Client $client */
    private $client;

    protected function setUp()
    {
        $this->client = static::createClient();
    }

    public function testSomething()
    {
        $this->client->request('GET', '/');

        var_dump($this->client->client->getResponse());

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
    }
}
