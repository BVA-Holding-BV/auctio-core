<?php
/**
 * API-information: https://api.sandbox.hexon.nl/spi/api/v2/rest/
 */
namespace AuctioCore\Api\Hexon;

class Api
{

    private $client;
    private $clientHeaders;
    private $messages;
    private $errorData;

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
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Authorization' => 'Basic ' . base64_encode($username . ":" . $password)
        ];

        // Set error-messages
        $this->messages = [];
        $this->errorData = [];
    }

    /**
     * Set error-data
     *
     * @param $data
     * @return array
     */
    public function setErrorData($data)
    {
        $this->errorData = $data;
    }

    /**
     * Get error-data
     *
     * @return array
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * Set error-message
     *
     * @param array $messages
     */
    public function setMessages($messages)
    {
        if (!is_array($messages)) $messages = [$messages];
        $this->messages = $messages;
    }

    /**
     * Add error-message
     *
     * @param array $message
     */
    public function addMessage($message)
    {
        if (!is_array($message)) $message = [$message];
        $this->messages = array_merge($this->messages, $message);
    }

    /**
     * Get error-messages
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Create vehicle
     *
     * @param mixed $data
     * @return boolean|array
     */
    public function createVehicle(\AuctioCore\Api\Hexon\Entity\Vehicle $data)
    {
        var_dump($body = $data->encode());

        exit;

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('POST', '/shipments/', ["headers"=>$requestHeader, "body"=>$body]);
        $response = json_decode((string) $result->getBody());
        if (!isset($response->errors)) {
            // Return
            return $response;
        } else {
            // Return
            $this->setErrorData($response);
            $this->setMessages($response->errors);
            return false;
        }
    }

}