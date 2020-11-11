<?php

/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom\Facade;

use Lotous\Elibom\Client;
use Illuminate\Support\Facades\Facade;

class Elibom extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        return Client::class;
    }
}
