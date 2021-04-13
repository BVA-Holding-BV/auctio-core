<?php
/**
 * API-information: https://docs.microsoft.com/nl-nl/azure/active-directory/develop/v2-oauth2-auth-code-flow
 */
namespace AuctioCore\Api\Microsoft;

use GuzzleHttp\Client;

class AzureApi
{

    private Client $client;
    private string $clientId;
    private string $clientSecret;
    private string $resource;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $clientId
     * @param string $clientSecret
     * @param string $resource
     * @param boolean $debug
     */
    public function __construct(string $hostname, string $clientId, string $clientSecret, $resource = 'https://graph.microsoft.com', $debug = false)
    {
        // Set client
        $this->client = new Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set client-credentials
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->resource = $resource;

        // Set error-messages
        $this->messages = [];
        $this->errorData = [];
    }

    /**
     * Set error-data
     *
     * @param array|string $data
     */
    public function setErrorData($data)
    {
        if (!is_array($data)) $data = [$data];
        $this->errorData = $data;
    }

    /**
     * Get error-data
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Set error-message
     *
     * @param array|string $messages
     */
    public function setMessages($messages)
    {
        if (!is_array($messages)) $messages = [$messages];
        $this->messages = $messages;
    }

    /**
     * Add error-message
     *
     * @param array|string $message
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
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Authorize user by Azure AD
     *
     * @param string $username
     * @param string $password
     * @return boolean|object
     */
    public function authorize(string $username, string $password)
    {
        // Check input parameters
        if (empty($username)) {
            $this->setMessages(["No username set"]);
            return false;
        } elseif (empty($password)) {
            $this->setMessages(["No password set"]);
            return false;
        }

        // Set parameters
        $parameters = [
            "grant_type" => "password",
            "client_id" => $this->clientId,
            "client_secret" => $this->clientSecret,
            "resource" => $this->resource,
            "username" => $username,
            "password" => $password,
        ];

        // Get token
        $result = $this->client->request('POST', 'oauth2/token', ["headers"=>["Content-Type"=>"application/x-www-form-urlencoded"], "form_params"=>$parameters]);
        $response = json_decode((string) $result->getBody());
        if ($result->getStatusCode() == 200) {
            // Return response
            return $response;
        } else {
            $this->setMessages("No token available");
            $this->setErrorData($response);
            return false;
        }
    }

}