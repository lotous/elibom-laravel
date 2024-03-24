<?php
/**
 * Elibom Client Library for PHP
 *
 * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
 * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
 */

namespace Lotous\Elibom;

use Composer\InstalledVersions;
use Lotous\Elibom\Client\Credentials\Container;
use Lotous\Elibom\Client\Credentials\SignatureSecret;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Laminas\Diactoros\Request;
use Lotous\Elibom\Client\Credentials\CredentialsInterface;
use Lotous\Elibom\Client\Credentials\Basic;
use RuntimeException;


class Client
{

    /**
     *
     */
    const VERSION = 'php-1.1';

    /**
     *
     */
    const BASE_API = 'https://www.elibom.com/';

    /**
     * @var
     */
    protected $client;

    /**
     * API Credentials
     * @var CredentialsInterface
     */
    protected $credentials;

    /**
     * Url API
     * @var String
     */
    protected $apiUrl;

    /**
     * Version API
     * @var String
     */
    protected $apiVersion;

    /**
     * @var array
     */
    protected $options = ['show_deprecations' => false];

    /**
     * @param CredentialsInterface $credentials
     * @param $options
     * @param ClientInterface|null $client
     */
    public function __construct(CredentialsInterface $credentials, $options = array(), ClientInterface $client = null)
    {


        if (is_null($client)) {
            // Since the user did not pass a client, try and make a client
            // using the Guzzle 6 adapter or Guzzle 7 (depending on availability)
            list($guzzleVersion) = explode('@', InstalledVersions::getVersion('guzzlehttp/guzzle'), 1);
            $guzzleVersion = (float) $guzzleVersion;

            if ($guzzleVersion >= 6.0 && $guzzleVersion < 7) {
                /** @noinspection CallableParameterUseCaseInTypeContextInspection */
                /** @noinspection PhpUndefinedNamespaceInspection */
                /** @noinspection PhpUndefinedClassInspection */
                $client = new \Http\Adapter\Guzzle6\Client();
            }

            if ($guzzleVersion >= 7.0 && $guzzleVersion < 8.0) {
                $client = new \GuzzleHttp\Client();
            }
        }

        $this->setHttpClient($client);

        if (
            !($credentials instanceof Container) &&
            !($credentials instanceof Basic) &&
            !($credentials instanceof SignatureSecret)
        ) {
            throw new RuntimeException('unknown credentials type: ' .  get_class($credentials));
        }

        $this->credentials = $credentials;
        $this->options = array_merge($this->options, $options);
        $this->apiUrl = static::BASE_API;
        $this->apiVersion = static::BASE_API;

        if (isset($options['api_url']) && !empty($options['api_version'])) {
            $this->apiUrl = $options['api_url'];
        }

        if (isset($options['api_version']) && !empty($options['api_version'])) {
            $this->apiVersion = $options['api_version'];
        }

        if (array_key_exists('show_deprecations', $this->options) && !$this->options['show_deprecations']) {
            set_error_handler(
                static function () {
                    return true;
                },
                E_USER_DEPRECATED
            );
        }
    }

    /**
     * @return mixed|String
     */
    public function getApiUrl()
    {
        return $this->apiUrl;
    }


    /**
     * @param ClientInterface $client
     * @return $this
     */
    public function setHttpClient(ClientInterface $client)
    {
        $this->client = $client;
        return $this;
    }


    /**
     * @return mixed
     */
    public function getHttpClient()
    {
        return $this->client;
    }


    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function get($url, array $params = [])
    {
        $queryString = '?' . http_build_query($params);

        $url = $this->apiUrl . "/" . $url . $queryString;

        $request = new Request(
            $url,
            'GET'
        );

        return $this->send($request);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function post($url, array $params)
    {
        $request = new Request(
            $this->apiUrl . "/" . $url,
            'POST',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));
        return $this->send($request);
    }

    /**
     * @param $url
     * @param array $params
     * @return mixed
     */
    public function put($url, array $params)
    {
        $request = new Request(
            $this->apiUrl . "/" . $url,
            'PUT',
            'php://temp',
            ['content-type' => 'application/json']
        );

        $request->getBody()->write(json_encode($params));
        return $this->send($request);
    }

    /**
     * @param $url
     * @return mixed
     */
    public function delete($url)
    {
        $request = new Request(
            $this->apiUrl . "/" . $url,
            'DELETE'
        );

        return $this->send($request);
    }


    /**
     * @param RequestInterface $request
     * @return mixed
     */
    public function send(RequestInterface $request)
    {
        if ($this->credentials instanceof Basic) {
            $c = $this->credentials->asArray();
            $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($c['api_key'] . ':' . $c['api_secret']));
        }

        //allow any part of the URI to be replaced with a simple search
        if (isset($this->options['api_url'])) {
            foreach ($this->options['api_url'] as $search => $replace) {
                $uri = (string)$request->getUri();

                $new = str_replace($search, $replace, $uri);
                if ($uri !== $new) {
                    $request = $request->withUri(new Uri($new));
                }
            }
        }

        // The user agent must be in the following format:
        // LIBRARY-NAME/LIBRARY-VERSION LANGUAGE-NAME/LANGUAGE-VERSION [APP-NAME/APP-VERSION]
        $userAgent = [];

        // Library name
        $userAgent[] = 'elibom-laravel/' . $this->getVersion();

        // Language name
        $userAgent[] = 'php/' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        // If we have an app set, add that to the UA
        if (isset($this->options['app'])) {
            $app = $this->options['app'];
            $userAgent[] = $app['name'] . '/' . $app['version'];
        }

        // Set the header. Build by joining all the parts we have with a space
        //$request = $request->withHeader('User-Agent', implode(" ", $userAgent));
        $request = $request->withHeader('X-API-Source', $this->apiVersion);
        return $this->client->send($request);
    }

    /**
     * @return mixed
     */
    protected function getVersion()
    {
        return InstalledVersions::getVersion('lotous/elibom-laravel');
    }

    /**
     * @param $to
     * @param $txt
     * @param $campaign
     * @return mixed
     */
    public function sendMessage($to, $txt, $campaign = null)
    {
        $data = array("to" => $to, "text" => $txt);
        if (isset($campaign)) {
            $data['campaign'] = $campaign;
        }
        return $this->post('messages', $data);
    }

    /**
     * @param $deliveryToken
     * @return mixed
     */
    public function getDelivery($deliveryToken)
    {
        return $this->get('messages/' . $deliveryToken);
    }

    /**
     * @param $to
     * @param $txt
     * @param $date
     * @param $campaign
     * @return mixed
     */
    public function scheduleMessage($to, $txt, $date, $campaign = null)
    {
        $data = array("to" => $to, "text" => $txt, "scheduleDate" => $date);
        if (isset($campaign)) {
            $data['campaign'] = $campaign;
        }
        return $this->post('messages', $data);
    }

    /**
     * @param $scheduleId
     * @return mixed
     */
    public function getScheduledMessage($scheduleId)
    {
        return $this->get('schedules/' . $scheduleId);
    }

    /**
     * @return mixed
     */
    public function getScheduledMessages()
    {
        return $this->get('schedules/scheduled');
    }

    /**
     * @param $scheduleId
     * @return mixed
     */
    public function unscheduleMessage($scheduleId)
    {
        return $this->delete('schedules/' . $scheduleId);
    }

    /**
     * @return mixed
     */
    public function getUsers()
    {
        return $this->get('users');
    }

    /**
     * @param $userId
     * @return mixed
     */
    public function getUser($userId)
    {
        return $this->get('users/' . $userId);
    }

    /**
     * @return mixed
     */
    public function getAccount()
    {
        return $this->get('account');
    }
}

?>
