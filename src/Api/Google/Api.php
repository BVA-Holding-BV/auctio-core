<?php
/**
 * API-information: https://cloud.google.com/translate/docs/
 */
namespace AuctioCore\Api\Google;

class Api
{

    private $apiKey;
    private $client;
    private $clientHeaders;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $apiKey
     */
    public function __construct($hostname, $apiKey)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri' => $hostname, 'http_errors' => false]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'X-HTTP-Method-Override' => 'GET',
        ];

        // Set api-key
        $this->apiKey = $apiKey;

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

    public function getTranslation($text, $sourceLanguage, $targetLanguage)
    {
        // Checks
        if (empty($text)) {
            $this->setMessages(["No source-text set"]);
            return false;
        }
        if (empty($sourceLanguage)) {
            $this->setMessages(["No source-language set"]);
            return false;
        }
        if (empty($targetLanguage)) {
            $this->setMessages(["No target-language set"]);
            return false;
        }

        // Prepare request
        $requestHeader = $this->clientHeaders;
        $request = [];
        $request['key'] = $this->_apiKey;
        $request['source'] = $sourceLanguage;
        $request['target'] = $targetLanguage;
        $request['q'] = urlencode($text);

        // Execute request
        $result = $this->client->request('POST', 'language/translate/v2', ["headers"=>$requestHeader, "body"=>json_encode($request)]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return $response;
            } else {
                $this->setErrorData($response);
                $this->setMessages($response->errors);
                return false;
            }
        } else {
            $response = json_decode((string) $result->getBody());
            $this->setErrorData($response);
            $this->setMessages([$result->getStatusCode() . ": " . $result->getReasonPhrase()]);
            return false;
        }
    }

}