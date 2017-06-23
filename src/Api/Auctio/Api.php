<?php

namespace AuctioCore\Api\Auctio;

class Api
{

    private $client;
    private $clientHeaders;
    private $accessToken;
    private $refreshToken;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     */
    public function __construct($hostname, $username, $password)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri' => $hostname, 'http_errors' => false]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];

        if (!empty($username)) {
            $this->login($username, $password);
        }
    }

    /**
     * Get access/refresh tokens by login
     *
     * @param $username
     * @param $password
     * @return array|bool
     */
    public function login($username, $password)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        $body = [
            'username'=>$username,
            'password'=>$password
        ];

        // Execute request
        $result = $this->client->request('POST', 'tokenlogin', ["headers"=>$requestHeader, "body"=>json_encode($body)]);
        if ($result->getStatusCode() == 201) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                // Set tokens in headers
                $this->clientHeaders['accessToken'] = $response->accessToken;
                $this->clientHeaders['refreshToken'] = $response->refreshToken;
                $this->clientHeaders['X-CSRF-Token'] = $response->csrfToken;

                return true;
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            return ["error"=>false, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>[]];
        }
    }

    /**
     * Logout token(s)
     *
     * @return array|bool
     */
    public function logout()
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'logout', ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                return true;
            } else {
                return ["error"=>true, "message"=>$response->errors, "data"=>[]];
            }
        } else {
            $response = json_decode((string) $result->getBody());
            return ["error"=>true, "message"=>$result->getStatusCode() . ": " . $result->getReasonPhrase(), "data"=>$response];
        }
    }

    public function createAuction(\AuctioCore\Api\Auctio\Entity\Auction $auction)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/auction', ["headers"=>$requestHeader, "body"=>$auction->encode()]);
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

    public function createLocation(\AuctioCore\Api\Auctio\Entity\Location $location)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/location', ["headers"=>$requestHeader, "body"=>$location->encode()]);
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

    public function createLot(\AuctioCore\Api\Auctio\Entity\Lot $lot)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/lot', ["headers"=>$requestHeader, "body"=>$lot->encode()]);
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

    public function createLotMedia($lotId, $lotSequence, $localFilename, $imageSequence)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        unset($requestHeader['Content-Type']);

        // Check file accessible/exists
        if (!file_exists($localFilename))
            throw new Exception('File not found: ' . $localFilename);
        if (!is_readable($localFilename))
            throw new Exception('File not readable: ' . $localFilename);

        // Set request-body
        $filename = $lotSequence;
        if ($imageSequence) $filename .= '-' . $imageSequence;
        $body = [[
            'name' => 'content',
            'filename'=> $localFilename,
            'contents' => fopen($localFilename, 'r')
        ],[
            'name' => 'fileName',
            'contents' => $filename . '.jpg'
        ]];

        // Execute request
        $result = $this->client->request('POST', 'ext123/lotmedia/' . $lotId, ["headers"=>$requestHeader, "multipart"=>$body]);
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

    public function createMainCategory(\AuctioCore\Api\Auctio\Entity\MainCategory $mainCategory)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/lotmaincategory', ["headers"=>$requestHeader, "body"=>$mainCategory->encode()]);
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

    public function createSubCategory(\AuctioCore\Api\Auctio\Entity\SubCategory $subCategory)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/lotsubcategory', ["headers"=>$requestHeader, "body"=>$subCategory->encode()]);
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

    public function getAuction($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId, ["headers"=>$requestHeader]);
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

    public function getAuctionCategories($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/nl/lotcategories/true/true", ["headers"=>$requestHeader]);
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

    public function getAuctionLocations($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/locations", ["headers"=>$requestHeader]);
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

    public function getLot($lotId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lot/' . $lotId, ["headers"=>$requestHeader]);
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

    public function getAuctionMainCategories($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/lotmaincategories", ["headers"=>$requestHeader]);
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