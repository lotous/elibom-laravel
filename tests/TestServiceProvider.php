<?php

namespace Lotous\Elibom\Tests;

use Lotous\Elibom\Client;

class TestServiceProvider extends AbstractTestCase
{
    /**
     * @param $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('elibom.api_key', 'my_api_key');
        $app['config']->set('elibom.api_secret', 'my_secret');
    }

    /**
     * @return void
     */
    public function testClientResolutionFromContainer()
    {
        $className = Client::class; // Asigna la clase que deseas resolver aquí
        $client = app($className);

        $this->assertInstanceOf($className, $client);
    }
}
