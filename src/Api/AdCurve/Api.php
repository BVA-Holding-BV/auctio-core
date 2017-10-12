<?php
/**
 * API-information: http://implementation.adcurve.com/api/v1/
 */
namespace AuctioCore\Api\AdCurve;

class Api
{

    private $client;
    private $clientHeaders;
    private $shopId;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $apiKey
     * @param string $shopId
     * @param boolean $debug
     */
    public function __construct($hostname, $apiKey, $shopId, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Api-Key' => $apiKey,
        ];

        // Set shop-id
        $this->shopId = $shopId;

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
     * @param object|array $products
     * @return array
     */
    public function createProducts($products)
    {
        // Check input
        if (is_array($products) && count($products) > 0) {
            foreach ($products AS $k => $product) {
                if (!($product instanceof \AuctioCore\Api\AdCurve\Entity\Product)) {
                    $this->setMessages("No valid input");
                    return false;
                } else {
                    $products[$k] = json_decode($product->encode());
                }
            }
        } else {
            if (!($products instanceof \AuctioCore\Api\AdCurve\Entity\Product)) {
                $this->setMessages("No valid input");
                return false;
            } else {
                $products = [json_decode($products->encode())];
            }
        }

        // Unset array-index
        $products = array_values($products);

        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'v1/shops/' . $this->shopId . '/shop_products/batch', ["headers"=>$requestHeader, "body"=>json_encode($products)]);
        if ($result->getStatusCode() == 200 || $result->getStatusCode() == 204) {
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
            $this->setMessages($result->getStatusCode() . ": " . $result->getReasonPhrase());
            return false;
        }
    }

    public function getProduct($variantId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'v2/shops/' . $this->shopId . '/shop_products/' . $variantId, ["headers"=>$requestHeader]);
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
            $this->setMessages($result->getStatusCode() . ": " . $result->getReasonPhrase());
            return false;
        }
    }

}