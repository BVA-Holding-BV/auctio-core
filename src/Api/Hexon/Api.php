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
     * @param boolean $debug
     */
    public function __construct($hostname, $username, $password, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Authorization' => 'Basic ' . base64_encode($username . ":" . $password),
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
     * Get body-styles
     *
     * @param string $language
     * @return boolean|array
     */
    public function getBodyStyles($language = "en_GB")
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('GET', 'bodystyles/', ["headers"=>$requestHeader]);
        $response = json_decode((string) $result->getBody());
        if (!isset($response->errors) || empty($response->errors)) {
            // Populate array of body-style
            $result = [];
            foreach ($response->results AS $res) {
                if (!isset($result[$res->category])) $result[$res->category] = [];

                // Iterate body-styles
                foreach ($res->bodystyle AS $bodystyle) {
                    // Skip if not selected language
                    if ($bodystyle->language != $language) continue;
                    // Add body-style to result
                    $result[$res->category][strtolower($bodystyle->translation)] = ucfirst($bodystyle->translation);
                    // Sort body-style results by name
                    ksort($result[$res->category]);
                }
            }

            // Return
            return $result;
        } else {
            // Return
            $this->setErrorData($response);
            $this->setMessages($response->errors);
            return false;
        }
    }

    /**
     * Get advertisement (single product can be advertised on several channels)
     *
     * @param int $stocknumber
     * $param string $site
     * @return boolean|array
     */
    public function getAdvertisement($stocknumber, $site)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('GET', 'ad/' . $stocknumber . ":" . $site, ["headers"=>$requestHeader]);
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

    /**
     * Get product
     *
     * @param int $stocknumber
     * @return boolean|array
     */
    public function getProduct($stocknumber)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('GET', 'vehicle/' . $stocknumber, ["headers"=>$requestHeader]);
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

    /**
     * Get product advertisements
     *
     * @param int $stocknumber
     * @return boolean|array
     */
    public function getProductAdvertisments($stocknumber)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('GET', 'vehicle/' . $stocknumber . '/ads/', ["headers"=>$requestHeader]);
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

    /**
     * Get product-image
     *
     * @param int $stocknumber
     * @param int $sequence
     * @return boolean|array
     */
    public function getProductImage($stocknumber, $sequence)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('GET', 'vehicleimage/' . $stocknumber . ':' . $sequence, ["headers"=>$requestHeader]);
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

    /**
     * Create advertisement (single product can be advertised on several channels)
     *
     * @param mixed $data
     * @return boolean|array
     */
    public function createAdvertisement(\AuctioCore\Api\Hexon\Entity\Advertisement $data)
    {
        // Convert input-data into body
        $body = $this->convertInput($data);

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('POST', 'ads/', ["headers"=>$requestHeader, "body"=>$body]);
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

    /**
     * Create product
     *
     * @param mixed $data
     * @return boolean|array
     */
    public function createProduct(\AuctioCore\Api\Hexon\Entity\Product $data)
    {
        // Convert input-data into body
        $body = $this->convertInput($data);

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('POST', 'vehicles/', ["headers"=>$requestHeader, "body"=>$body]);
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

    /**
     * Create product-image
     *
     * @param mixed $data
     * @return boolean|array
     */
    public function createProductImage(\AuctioCore\Api\Hexon\Entity\ProductImage $data)
    {
        // Convert input-data into body
        $body = $this->convertInput($data);

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('POST', 'vehicleimages/', ["headers"=>$requestHeader, "body"=>$body]);
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

    /**
     * Delete advertisement (single product can be advertised on several channels)
     *
     * @param int $stocknumber
     * @param string $site
     * @return boolean|array
     */
    public function deleteAdvertisement($stocknumber, $site)
    {
        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('DELETE', 'ad/' . $stocknumber . ":" . $site, ["headers"=>$requestHeader]);
        $response = json_decode((string) $result->getBody());

        if (!isset($response->errors) || empty($response->errors) && strtolower($result->getReasonPhrase()) == 'delete ok') {
            // Return
            return true;
        } else {
            // Return
            $this->setErrorData($response);
            $this->setMessages($response->errors);
            return false;
        }
    }

    /**
     * Update product
     *
     * @param int $stocknumber
     * @param mixed $data
     * @return boolean|array
     */
    public function updateProduct($stocknumber, \AuctioCore\Api\Hexon\Entity\Product $data)
    {
        // Convert input-data into body
        $body = $this->convertInput($data);

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('PUT', 'vehicle/' . $stocknumber, ["headers"=>$requestHeader, "body"=>$body]);
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

    /**
     * Update product-image
     *
     * @param int $stocknumber
     * @param int $sequence
     * @param mixed $data
     * @return boolean|array
     */
    public function updateProductImage($stocknumber, $sequence, \AuctioCore\Api\Hexon\Entity\ProductImage $data)
    {
        // Convert input-data into body
        $body = $this->convertInput($data);

        $requestHeader = $this->clientHeaders;
        $result = $this->client->request('PUT', 'vehicleimages/' . $stocknumber . ':' . $sequence, ["headers"=>$requestHeader, "body"=>$body]);
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

    /**
     * Convert input-data to body
     *
     * @param mixed $data
     * @return mixed
     */
    public function convertInput ($data) {
        // Create JSON-string
        $body = $data->encode();
        // Decode JSON-string into array
        $body = json_decode($body, true);
        foreach ($body AS $k => $v) {
            // Get capital characters
            preg_match_all('/[A-Z]/', $k, $matches, PREG_OFFSET_CAPTURE);
            if (!empty($matches[0])) {
                $key = $k;
                foreach ($matches[0] AS $match) {
                    // Replace all capital characters into dot-lowercase character (example identificationStocknumber_public -> identification.stocknumber_public)
                    $key = str_replace($match[0], "." . strtolower($match[0]), $key);
                }
                $body[$key] = $v;
                unset($body[$k]);
            }
        }
        // Encode array into JSON-string
        $body = json_encode($body);

        return $body;
    }

}