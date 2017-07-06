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

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $apiKey
     * @param string $shopId
     */
    public function __construct($hostname, $apiKey, $shopId)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri' => $hostname, 'http_errors' => false]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-Api-Key' => $apiKey,
        ];

        // Set shop-id
        $this->shopId = $shopId;
    }

    /**
     * @param object|array $products
     * @return array
     */
    public function createProducts($products)
    {
        // Check input
        if (is_array($products) && count($products) > 0) {
            foreach ($products AS $product) {
                if (!($product instanceof \AuctioCore\Api\AdCurve\Entity\Product)) {
                    return ["error"=>true, "message"=>"No valid input"];
                }
            }
        } else {
            if (!($products instanceof \AuctioCore\Api\AdCurve\Entity\Product)) {
                return ["error"=>true, "message"=>"No valid input"];
            } else {
                $products = [$products];
            }
        }

        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', '/v1/shops/' . $this->shopId . '/shop_products/batch', ["headers"=>$requestHeader, "body"=>$products->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return ["error"=>false, "message"=>"Ok", "data"=>$response];
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            $response = json_decode((string) $result->getBody());
            return ["error"=>true, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>$response];
        }
    }

}