<?php

namespace AuctioCore\Api;

class Auctio
{

    private $client;
    private $clientHeaders;

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

        if (!empty($username)) {
            $this->login($username, $password);
        }

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    public function login($username, $password)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        $body = [
            'username'=>$username,
            'password'=>$password
        ];

        // Execute request
        //$result = $this->client->request('POST', 'ext123/login', ["headers"=>$requestHeader, "body"=>json_encode($body)]);
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
            return ["error"=>false, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>[]];
        }

    }
}