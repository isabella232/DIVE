<?php

namespace Dive\APIBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DataControllerTest extends WebTestCase
{
    public function testInfo()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/info');
    }

}
