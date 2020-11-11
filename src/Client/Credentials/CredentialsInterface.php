<?php
/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom\Client\Credentials;

interface CredentialsInterface extends \ArrayAccess
{
    public function asArray();
}
