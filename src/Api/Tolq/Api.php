<?php
/**
 * API-information: http://docs.tolq.com/docs
 */
namespace AuctioCore\Api\Tolq;

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
     * @param string $access_key
     * @param string $secret
     * @param boolean $debug
     */
    public function __construct($hostname, $access_key, $secret, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Authorization' => 'Basic ' . base64_encode($access_key . ":" . $secret),
            'Content-Type' => 'application/json',
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
     * Get translation
     *
     * @param string $text
     * @param string $sourceLanguage
     * @param string $targetLanguage
     * @param array $options
     * @return boolean|array
     */
    public function getTranslation($text, $sourceLanguage, $targetLanguage, $options = null)
    {
        // Check input parameters
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
        if (empty($options['quality'])) {
            $this->setMessages(["No quality set"]);
            return false;
        }

        // Get variables from options
        $quality = $options['quality'];

        // Prepare request
        $data = [];
        $data['source_language_code'] = $sourceLanguage;
        $data['target_language_codes'] = $targetLanguage;
        $data['quality'] = $quality;
        $data['request'] = [];
        foreach ($text AS $field => $fieldText) {
            $data['request'][$field] = ["text"=>$fieldText];
        }

        // Check input parameters
        if (empty($data['request'])) {
            $this->setMessages(["No source-text set"]);
            return false;
        }

        $requestData = new \AuctioCore\Api\Tolq\Entity\Request($data);
        $requestData->options = new \AuctioCore\Api\Tolq\Entity\RequestOptions($options);

        // Execute request
        return $this->createRequest($requestData);
    }

    /**
     * Create translation-request
     *
     * @param mixed $data
     * @return boolean|array
     */
    public function createRequest(\AuctioCore\Api\Tolq\Entity\Request $data)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('POST', 'requests', ["headers"=>$requestHeader, "body"=>$data->encode()]);
        $response = json_decode((string) $result->getBody());
        if (!isset($response->errors) || empty($response->errors)) {
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