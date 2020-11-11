<?php
/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom\Client\Credentials;

class SignatureSecret extends AbstractCredentials implements CredentialsInterface
{
    /**
     * Create a credential set with an API key and signature secret.
     *
     * @param string $key    API Key
     * @param string $signature_secret Signature Secret
     */
    public function __construct($key, $signature_secret, $method = 'md5hash')
    {
        $this->credentials['api_key'] = $key;
        $this->credentials['signature_secret'] = $signature_secret;
        $this->credentials['signature_method'] = $method;
    }
}
