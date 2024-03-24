<?php

namespace Lotous\Elibom\Tests;

use Orchestra\Testbench\TestCase;
use Lotous\Elibom\ElibomServiceProvider;
use Lotous\Elibom\Client;

abstract class AbstractTestCase extends TestCase
{
    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageProviders($app)
    {
        return [
            ElibomServiceProvider::class,
        ];
    }

    /**
     * @param $app
     * @return string[]
     */
    protected function getPackageAliases($app)
    {
        return [
            'Elibom' => \Lotous\Elibom\Facade\Elibom::class,
        ];
    }

    /**
     * @param $class
     * @param $property
     * @param $object
     * @return mixed
     * @throws \ReflectionException
     */
    public function getClassProperty($class, $property, $object)
    {
        $reflectionClass = new \ReflectionClass($class);
        $refProperty = $reflectionClass->getProperty($property);
        $refProperty->setAccessible(true);

        return $refProperty->getValue($object);
    }

    /**
     * @return array[]
     */
    public function classNameProvider()
    {
        return [
            [Client::class]
        ];
    }
}
