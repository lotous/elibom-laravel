<?php
/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom\Client\Credentials;

/**
 * Class Basic
 * Read-only container for api key and secret.
 */
class Basic extends AbstractCredentials implements CredentialsInterface
{
    /**
     * Create a credential set with an API key and secret.
     *
     * @param string $key
     * @param string $secret
     */
    public function __construct($key, $secret)
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['api_secret'] = $secret;
    }
}
