<?php
    /**
     * Elibom Client Library for PHP
     *
     * @copyright Copyright (c) 2020 Lotous, Inc. (https://lotous.com.co)
     * @license   https://github.com/lotous/elibom/blob/master/LICENSE MIT License
     */

    namespace Lotous\Elibom;

    use Psr\Http\Message\RequestInterface;
    use Psr\Http\Client\ClientInterface;
    use Http\Client\HttpClient;
    use Psr\Http\Message\ResponseInterface;
    use Zend\Diactoros\Request;
    use Lotous\Elibom\Client\Credentials\CredentialsInterface;
    use Lotous\Elibom\Client\Credentials\Basic;
    use Illuminate\Support\Facades\Log;


    class Client {

        /**
         *
         */
        const VERSION = 'php-1.1';

        /**
         *
         */
        const BASE_API  = 'https://www.elibom.com/';

        /**
         * Http Client
         * @var HttpClient
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
         * APIClient constructor.
         * @param CredentialsInterface | Basic $credentials
         * @param array $options
         * @param ClientInterface|null $client
         */
        public function __construct(CredentialsInterface $credentials, $options = array(), ClientInterface $client = null) {

            if (is_null($client)) {
                // Since the user did not pass a client, try and make a client
                // using the Guzzle 6 adapter or Guzzle 7
                if (class_exists(\Http\Adapter\Guzzle6\Client::class)) {
                    $client = new \Http\Adapter\Guzzle6\Client();
                } elseif (class_exists(\GuzzleHttp\Client::class)) {
                    $client = new \GuzzleHttp\Client();
                }
            }

            $this->setHttpClient($client);

            $this->credentials = $credentials;

            $this->options = array_merge($this->options, $options);

            // Set the default URLs. Keep the constants for
            // backwards compatibility
            $this->apiUrl = static::BASE_API;
            $this->apiVersion = static::BASE_API;

            if (isset($options['api_url'])  && !empty($options['api_version'])) {
                $this->apiUrl = $options['api_url'];
            }

            if (isset($options['api_version']) && !empty($options['api_version'])) {
                $this->apiVersion = $options['api_version'];
            }

            if (array_key_exists('show_deprecations', $this->options) && !$this->options['show_deprecations']) {
                set_error_handler(
                    function (
                        int $errno,
                        string $errstr,
                        string $errfile,
                        int $errline,
                        array $errorcontext
                    ) {
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
         * Set the Http Client to used to make API requests.
         *
         * This allows the default http client to be swapped out for a HTTPlug compatible
         * replacement.
         *
         * @param HttpClient $client
         * @return $this
         */
        public function setHttpClient(ClientInterface $client)
        {
            $this->client = $client;
            return $this;
        }


        /**
         * Get the Http Client used to make API requests.
         *
         * @return HttpClient
         */
        public function getHttpClient()
        {
            return $this->client;
        }


        /**
         * Takes a URL and a key=>value array to generate a GET PSR-7 request object
         *
         * @param string $url The URL to make a request to
         * @param array $params Key=>Value array of data to use as the query string
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function get($url, array $params = [])
        {
            $queryString = '?' . http_build_query($params);

            $url = $this->apiUrl."/". $url . $queryString;

            $request = new Request(
                $url,
                'GET'
            );

            return $this->send($request);
        }

        /**
         * Takes a URL and a key=>value array to generate a POST PSR-7 request object
         *
         * @param string $url The URL to make a request to
         * @param array $params Key=>Value array of data to send
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function post($url, array $params)
        {
            $request = new Request(
                $this->apiUrl."/".$url,
                'POST',
                'php://temp',
                ['content-type' => 'application/json']
            );

            $request->getBody()->write(json_encode($params));
            return $this->send($request);
        }

        /**
         * Takes a URL and a key=>value array to generate a PUT PSR-7 request object
         *
         * @param string $url The URL to make a request to
         * @param array $params Key=>Value array of data to send
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function put($url, array $params)
        {
            $request = new Request(
                $this->apiUrl."/".$url,
                'PUT',
                'php://temp',
                ['content-type' => 'application/json']
            );

            $request->getBody()->write(json_encode($params));
            return $this->send($request);
        }

        /**
         * Takes a URL and a key=>value array to generate a DELETE PSR-7 request object
         *
         * @param string $url The URL to make a request to
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function delete($url)
        {
            $request = new Request(
                $this->apiUrl."/".$url,
                'DELETE'
            );

            return $this->send($request);
        }


        /**
         * Wraps the HTTP Client, creates a new PSR-7 request adding authentication, signatures, etc.
         *
         * @param \Psr\Http\Message\RequestInterface $request
         * @return \Psr\Http\Message\ResponseInterface
         */
        public function send(\Psr\Http\Message\RequestInterface $request)
        {
            if ($this->credentials instanceof Basic) {
                $c = $this->credentials->asArray();
                $request = $request->withHeader('Authorization', 'Basic ' . base64_encode($c['api_key'] . ':' . $c['api_secret']));
            }

            //allow any part of the URI to be replaced with a simple search
            if (isset($this->options['api_url'])) {
                foreach ($this->options['api_url'] as $search => $replace) {
                    $uri = (string) $request->getUri();

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
            $userAgent[] = 'elibom-laravel/'.$this->getVersion();

            // Language name
            $userAgent[] = 'php/'.PHP_MAJOR_VERSION.'.'.PHP_MINOR_VERSION;

            // If we have an app set, add that to the UA
            if (isset($this->options['app'])) {
                $app = $this->options['app'];
                $userAgent[] = $app['name'].'/'.$app['version'];
            }

            // Set the header. Build by joining all the parts we have with a space
            $request = $request->withHeader('User-Agent', implode(" ", $userAgent));
            $request = $request->withHeader('X-API-Source', $this->apiVersion);

            Log::info(print_r($request, true));

            return $this->client->sendRequest($request);
        }

        /**
         * @return mixed
         */
        protected function getVersion()
        {
            return \PackageVersions\Versions::getVersion('lotous/elibom-laravel');
        }

        /**
         * @param $to
         * @param $txt
         * @param null $campaign
         * @return ResponseInterface
         */
        public function sendMessage($to, $txt, $campaign = null) {
            $data = array("destinations" => $to, "text" => $txt);
            if (isset($campaign)) {
                $data['campaign'] = $campaign;
            }
            return $this->post('messages', $data);
        }

        /**
         * @param $deliveryToken
         * @return ResponseInterface
         */
        public function getDelivery($deliveryToken) {
            return $this->get('messages/' . $deliveryToken);
        }

        /**
         * @param $to
         * @param $txt
         * @param $date
         * @param null $campaign
         * @return ResponseInterface
         */
        public function scheduleMessage($to, $txt, $date, $campaign = null) {
            $data = array("destinations" => $to, "text" => $txt, "scheduleDate" => $date);
            if (isset($campaign)) {
                $data['campaign'] = $campaign;
            }
            return $this->post('messages', $data);
        }

        /**
         * @param $scheduleId
         * @return ResponseInterface
         */
        public function getScheduledMessage($scheduleId) {
            return $this->get('schedules/' . $scheduleId);
        }

        /**
         * @return ResponseInterface
         */
        public function getScheduledMessages() {
            return $this->get('schedules/scheduled');
        }

        /**
         * @param $scheduleId
         * @return ResponseInterface
         */
        public function unscheduleMessage($scheduleId) {
            return  $this->delete('schedules/' . $scheduleId);
        }

        /**
         * @return mixed
         */
        public function getUsers() {
            return $this->get('users');
        }

        /**
         * @param $userId
         * @return mixed
         */
        public function getUser($userId) {
            return  $this->get('users/' . $userId);
        }

        /**
         * @return ResponseInterface
         */
        public function getAccount() {
            return $this->get('account');
        }
    }

?>