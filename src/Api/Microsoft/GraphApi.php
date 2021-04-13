<?php
/**
 * API-information: https://docs.microsoft.com/en-us/graph/azuread-identity-access-management-concept-overview
 */
namespace AuctioCore\Api\Microsoft;

use GuzzleHttp\Client;

class GraphApi
{

    private Client $client;
    private string $token;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $token
     * @param boolean $debug
     */
    public function __construct(string $hostname, string $token, $debug = false)
    {
        // Set client
        $this->client = new Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set token
        $this->token = $token;

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
     * Get profile information of authorized user
     *
     * @return boolean|object
     */
    public function getProfile()
    {
        // Set fields
        $fields = ["id", "userPrincipalName", "givenName", "surname", "displayName", "companyName", "department",
            "jobTitle", "mail", "businessPhones", "mobilePhone", "officeLocation", "preferredLanguage"];

        // Get profile
        $result = $this->client->request('GET', 'v1.0/me?$select=' . implode(",", $fields), ["headers"=>["Authorization"=>$this->token]]);
        $response = json_decode((string) $result->getBody());
        if ($result->getStatusCode() == 200) {
            // Return response
            return $response;
        } else {
            $this->setMessages($response->error->code . ": " . $response->error->message);
            $this->setErrorData($response);
            return false;
        }
    }

    /**
     * Get roles of authorized user
     *
     * @return boolean|object
     */
    public function getRoles()
    {
        // Get profile
        $result = $this->client->request('GET', 'v1.0/me/memberOf', ["headers"=>["Authorization"=>$this->token]]);
        $response = json_decode((string) $result->getBody());
        if ($result->getStatusCode() == 200) {
            // Return response
            return $response;
        } else {
            $this->setMessages($response->error->code . ": " . $response->error->message);
            $this->setErrorData($response);
            return false;
        }
    }
}