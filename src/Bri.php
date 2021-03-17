<?php

namespace Aslam\Bri;

use Aslam\Bri\Traits;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;

class Bri
{
    use Traits\Token;
    use Traits\Information;
    use Traits\BRIVA;

    /**
     * apiUrlV1
     *
     * @var mixed
     */
    protected $apiUrlV1;

    /**
     * apiUrlV2
     *
     * @var mixed
     */
    protected $apiUrlV2;

    /**
     * clientID
     *
     * @var mixed
     */
    protected $clientID;

    /**
     * clientSecret
     *
     * @var mixed
     */
    protected $clientSecret;

    /**
     * endpoint
     *
     * @var mixed
     */
    protected $endpoint;

    /**
     * Token
     *
     * @var mixed
     */
    protected $token;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($token = null)
    {
        $this->apiUrlV1 = config('bank-bri.api_url_v1');
        $this->apiUrlV2 = config('bank-bri.api_url_v2');
        $this->clientID = config('bank-bri.client_id');
        $this->clientSecret = config('bank-bri.client_secret');
        $this->endpoint = (object) config('bank-bri.endpoint');

        $this->token = $token;
    }

    /**
     * Send the request to the given URL.
     *
     * @param  string $httpMethod
     * @param  string $requestUrl
     * @param  array $options
     * @return \Aslam\Bri\Response
     *
     * @throws
     */
    public function sendRequest(string $httpMethod, string $requestUrl, array $data = [])
    {
        try {

            $options = [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->token,
                    'BRI-Timestamp' => get_timestamp(),
                    'BRI-Signature' => get_hmac_signature($requestUrl, $httpMethod, $this->token, $data),
                ],
                'http_errors' => false,
            ];

            $method = strtoupper($httpMethod);

            if ($method === 'POST' || $method === 'PUT' || $method === 'PATCH') {
                $options['headers']['Content-Type'] = 'application/json';
                $options['json'] = $data;
            }

            $client = tap(
                new Response(
                    (new Client())->request($httpMethod, $requestUrl, $options)
                ),
                function ($response) {
                    if (!$response->successful()) {
                        $response->throw();
                    }
                }
            );

            return $client;

        } catch (ConnectException $e) {
            throw new ConnectionException($e->getMessage(), 0, $e);
        } catch (RequestException $e) {
            return $e->response;
        }
    }

    /**
     * setToken
     *
     * @param  string $token
     * @return void
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }
}
