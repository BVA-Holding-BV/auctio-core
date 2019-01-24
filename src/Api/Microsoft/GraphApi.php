<?php
/**
 * API-information: https://docs.microsoft.com/en-us/graph/azuread-identity-access-management-concept-overview
 */
namespace AuctioCore\Api\Microsoft;

class GraphApi
{

    private $client;
    private $token;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $clientId
     * @param string $clientSecret
     * @param boolean $debug
     */
    public function __construct($hostname, $token, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set token
        $this->token = $token;

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