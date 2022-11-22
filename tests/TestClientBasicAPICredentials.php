<?php

namespace Lotous\Elibom\Tests;

use Lotous\Elibom\Client;
use Lotous\Elibom\Client\Credentials\Basic;

class TestClientBasicAPICredentials extends AbstractTestCase
{
    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('elibom.api_key', 'my_api_key');
        $app['config']->set('elibom.api_secret', 'my_secret');
    }

    /**
     * Test that our Nexmo client is created with
     * the Basic API credentials.
     *
     * @return void
     */
    public function testClientCreatedWithBasicAPICredentials()
    {
        $client = app(Client::class);
        $credentialsObject = $this->getClassProperty(Client::class, 'credentials', $client);
        $credentialsArray = $this->getClassProperty(Basic::class, 'credentials', $credentialsObject);

        $this->assertInstanceOf(Basic::class, $credentialsObject);
        $this->assertEquals(['api_key' => 'my_api_key', 'api_secret' => 'my_secret'], $credentialsArray);
    }
}
