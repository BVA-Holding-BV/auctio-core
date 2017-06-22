<?php

namespace AuctioCore\Api\Auctio;

use AuctioCore\Api\Auctio\Entity\Auction;

class Api
{

    private $client;
    private $clientHeaders;
    private $accessToken;
    private $refreshToken;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     */
    public function __construct($hostname, $username, $password)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri' => $hostname, 'http_errors' => false]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($username)) {
            $this->login($username, $password);
        }
    }

    /**
     * Get access/refresh tokens by login
     *
     * @param $username
     * @param $password
     * @return array|bool
     */
    public function login($username, $password)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        $body = [
            'username'=>$username,
            'password'=>$password
        ];

        // Execute request
        $result = $this->client->request('POST', 'tokenlogin', ["headers"=>$requestHeader, "body"=>json_encode($body)]);
        if ($result->getStatusCode() == 201) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                // Set tokens in headers
                $this->clientHeaders['accessToken'] = $response->accessToken;
                $this->clientHeaders['refreshToken'] = $response->refreshToken;
                $this->clientHeaders['X-CSRF-Token'] = $response->csrfToken;

                return true;
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            return ["error"=>false, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>[]];
        }
    }

    /**
     * Logout token(s)
     *
     * @return array|bool
     */
    public function logout()
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'logout', ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return true;
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            $response = json_decode((string) $result->getBody());
            return ["error"=>true, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>$response];
        }
    }

    public function createAuction(Auction $auction)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/auction', ["headers"=>$requestHeader, "body"=>$auction->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return ["error"=>false, "message"=>"Ok", "data"=>$response];
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            $response = json_decode((string) $result->getBody());
            return ["error"=>true, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>$response];
        }

    }

    public function getAuction($id)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $id, ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return ["error"=>false, "message"=>"Ok", "data"=>$response];
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            $response = json_decode((string) $result->getBody());
            return ["error"=>true, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>$response];
        }

    }
}