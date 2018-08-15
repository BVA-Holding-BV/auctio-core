<?php
/**
 * API-information: https://www.deepl.com/api.html
 */
namespace AuctioCore\Api\DeepL;

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
     * @param boolean $debug
     */
    public function __construct($hostname, $apiKey, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set default header for client-requests
        $this->clientHeaders = [];

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

        // Prepare request-header
        $requestHeader = $this->clientHeaders;
        $requestHeader['Content-Type'] = 'application/x-www-form-urlencoded';

        // Prepare request
        $request = [];
        $request['auth_key'] = $this->apiKey;
        $request['source_lang'] = $sourceLanguage;
        $request['target_lang'] = $targetLanguage;
        $request['text'] = utf8_encode($text);

        // Execute request
        $result = $this->client->request('POST', 'translate', ["headers"=>$requestHeader, "form_params"=>$request]);
        $response = json_decode((string) $result->getBody());
        if ($result->getStatusCode() == 200) {
            // Return
            if (!isset($response->message)) {
                // Check if translation available
                if (!is_array($response->translations)) {
                    $this->setErrorData($response);
                    $this->setMessages(["No translation available"]);
                    return false;
                } else {
                    // Get translation (from response)
                    $translation = current($response->translations)->text;

                    // Set whitespaces (before) text in translation (these were filtered)
                    if (preg_match("/^\s/", $text)) {
                        $translation = " " . $translation;
                    }

                    // Return
                    return $translation;
                }
            } else {
                $this->setErrorData($response);
                $this->setMessages($response->message);
                return false;
            }
        } else {
            $this->setErrorData($response);
            $this->setMessages([$result->getStatusCode() . ": " . $result->getReasonPhrase()]);
            return false;
        }
    }

}