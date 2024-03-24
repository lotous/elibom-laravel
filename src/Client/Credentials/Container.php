<?php
/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom\Client\Credentials;

class Container extends AbstractCredentials implements CredentialsInterface
{
    protected $types = [
        Basic::class,
        SignatureSecret::class
    ];

    protected $credentials;

    public function __construct($credentials)
    {
        if (!is_array($credentials)) {
            $credentials = func_get_args();
        }

        foreach ($credentials as $credential) {
            $this->addCredential($credential);
        }
    }

    protected function addCredential(CredentialsInterface $credential)
    {
        $type = $this->getType($credential);
        if (isset($this->credentials[$type])) {
            throw new \RuntimeException('can not use more than one of a single credential type');
        }

        $this->credentials[$type] = $credential;
    }

    protected function getType(CredentialsInterface $credential)
    {
        if ($credential instanceof Basic) {
            return Basic::class;
        }

        if ($credential instanceof SignatureSecret) {
            return SignatureSecret::class;
        }

        throw new \RuntimeException('credential type not supported for elibom client library');
    }

    public function get($type)
    {
        if (!isset($this->credentials[$type])) {
            throw new \RuntimeException('credental not set');
        }

        return $this->credentials[$type];
    }

    public function has($type)
    {
        return isset($this->credentials[$type]);
    }

}
