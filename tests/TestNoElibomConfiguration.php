<?php

namespace Lotous\Elibom\Tests;

class TestNoElibomConfiguration extends AbstractTestCase
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
    }

    /**
     * Test that when we do not supply Elibom configuration
     * a Runtime exception is generated under the Elibom namespace.
     *
     * @dataProvider classNameProvider
     *
     * @return void
     */
    public function testWhenNoConfigurationIsGivenExceptionIsRaised($className)
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Please provide Elibom API credentials. Possible combinations: api_key + api_secret');

        app($className);
    }
}
