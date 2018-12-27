<?php
/**
 * API-information: https://docs.microsoft.com/nl-nl/azure/active-directory/develop/v2-oauth2-auth-code-flow
 */
namespace AuctioCore\Api\Microsoft;

class AzureApi
{

    private $client;
    private $clientId;
    private $clientSecret;
    private $resource;
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
    public function __construct($hostname, $clientId, $clientSecret, $resource = 'https://graph.microsoft.com', $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

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
     * Authorize user by Azure AD
     *
     * @param string $username
     * @param string $password
     * @return boolean|object
     */
    public function authorize($username, $password)
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