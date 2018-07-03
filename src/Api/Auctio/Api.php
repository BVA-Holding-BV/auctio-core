<?php
/**
 * API-information: https://api.bva-auctions.com/api/docs/
 */
namespace AuctioCore\Api\Auctio;

class Api
{

    private $errorData;
    private $client;
    private $clientHeaders;
    private $messages;
    private $token;
    private $tz;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $userAgent
     * @param object|array $token
     * @param boolean $debug
     */
    public function __construct($hostname, $username = null, $password = null, $userAgent = null, $token = null, $debug = false)
    {
        // Set time-zone for converting "back" from UTC
        $this->tz = new \DateTimeZone('Europe/Amsterdam');

        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Accept' => 'application/json',
            'Accept-Encoding' => 'gzip',
            'Content-Type' => 'application/json',
            'User-Agent' => $userAgent,
        ];

        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        if (!empty($username) && empty($token)) {
            $this->login($username, $password);
        } elseif (!empty($token)) {
            $this->clientHeaders['accessToken'] = (is_object($token)) ? $token->accessToken : $token['accessToken'];
            $this->clientHeaders['refreshToken'] = (is_object($token)) ? $token->refreshToken : $token['refreshToken'];
            $this->clientHeaders['X-CSRF-Token'] = (is_object($token)) ? $token->csrfToken : $token['csrfToken'];
        }
    }

    /**
     * Set error-data
     *
     * @param array $data
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
     * Set token
     *
     * @param array $data
     */
    public function setToken($data)
    {
        $this->token = $data;
    }

    /**
     * Get token
     *
     * @return array
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Get access/refresh tokens by login
     *
     * @param string $username
     * @param string $password
     * @param boolean $retry
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function login($username, $password, $retry = true)
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
                // Save token
                $this->setToken([
                    'accessToken' => $response->accessToken,
                    'refreshToken' => $response->refreshToken,
                    'csrfToken' => $response->csrfToken
                ]);

                // Set tokens in headers
                $this->clientHeaders['accessToken'] = $response->accessToken;
                $this->clientHeaders['refreshToken'] = $response->refreshToken;
                $this->clientHeaders['X-CSRF-Token'] = $response->csrfToken;
                return true;
            } else {
                $this->setErrorData($response);
                $this->setMessages($response->errors);
                return false;
            }
        } elseif ($result->getStatusCode() == 409 && $retry !== false) {
            $retry = (is_bool($retry)) ? 1 : ($retry + 1);
            if ($retry > 5) $retry = false;
            return $this->login($username, $password, $retry);
        } else {
            $response = json_decode((string) $result->getBody());
            $this->setErrorData($response);
            $this->setMessages([$result->getStatusCode() . ": " . $result->getReasonPhrase()]);
            return true;
        }
    }

    /**
     * Logout token(s)
     *
     * @return boolean
     * @throws \GuzzleHttp\Exception\GuzzleException
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

    /**
     * Create auction
     *
     * @param Entity\Auction $auction
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
                $response = $this->convertDates($response, ["creationDate", "startDate", "endDate"]);
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

    /**
     * Create auction collection-day
     *
     * @param Entity\CollectionDay $day
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createCollectionDay(\AuctioCore\Api\Auctio\Entity\CollectionDay $day)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/lotcollectionday', ["headers"=>$requestHeader, "body"=>$day->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    /**
     * Create auction display-day
     *
     * @param Entity\DisplayDay $day
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDisplayDay(\AuctioCore\Api\Auctio\Entity\DisplayDay $day)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'ext123/lotdisplayday', ["headers"=>$requestHeader, "body"=>$day->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    /**
     * Create auction-location
     *
     * @param Entity\Location $location
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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

    /**
     * Create lot
     *
     * @param Entity\Lot $lot
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
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
                $response = $this->convertDates($response, ["startDate", "endDate", "lastBidTime"]);
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

    /**
     * Upload lot-media
     *
     * @param int $lotId
     * @param int $lotSequence
     * @param string $localFilename
     * @param int $imageSequence
     * @param array $uploadFile
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createLotMedia($lotId, $lotSequence, $localFilename, $imageSequence, $uploadFile = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        unset($requestHeader['Content-Type']);

        // Check file accessible/exists
        if (!file_exists($localFilename)) {
            $this->setMessages(['File not found: ' . $localFilename]);
            return false;
        } elseif (!is_readable($localFilename)) {
            $this->setMessages(['File not readable: ' . $localFilename]);
            return false;
        }

        // Get mime-type
        $mimeType = mime_content_type($localFilename);
        if ($mimeType == 'application/octet-stream' && !empty($uploadFile)) {
            $mimeType = $uploadFile['type'];
        }

        // Set file extension
        if ($mimeType == 'video/mp4') {
            $extension = "mp4";
        } elseif ($mimeType == 'image/jpeg') {
            $extension = "jpg";
        } else {
            $this->setMessages(['Unknown mime-type: ' . $mimeType]);
            return false;
        }

        // Set request-body
        $filename = $lotSequence;
        if ($imageSequence) $filename .= '-' . $imageSequence;
        $body = [[
            'name' => 'content',
            'filename'=> $localFilename,
            'contents' => fopen($localFilename, 'r')
        ],[
            'name' => 'fileName',
            'contents' => $filename . '.' . $extension
        ]];

        // Execute request
        $result = $this->client->request('POST', 'ext123/lotmedia/' . $lotId, ["headers"=>$requestHeader, "multipart"=>$body]);
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

    private function createLotMetaData(\AuctioCore\Api\Auctio\Entity\LotMetaData $lotMetaData)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Check empty lot-metadata keys (unset them to avoid error "Metadata body cannot be empty")
        if (is_array($lotMetaData->metadata)) {
            foreach ($lotMetaData->metadata AS $k => $v) {
                if (empty($v->value)) {
                    unset($lotMetaData->metadata[$k]);
                }
            }

            // Reset array-keys
            $lotMetaData->metadata = array_values($lotMetaData->metadata);
        }

        // Execute request
        $result = $this->client->request('POST', 'lot-metadata', ["headers"=>$requestHeader, "body"=>$lotMetaData->encode()]);
        if (in_array($result->getStatusCode(), [200,201])) {
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

    public function updateAuction(\AuctioCore\Api\Auctio\Entity\Auction $auction)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'ext123/auction', ["headers"=>$requestHeader, "body"=>$auction->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["creationDate", "startDate", "endDate"]);
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

    public function updateCollectionDay(\AuctioCore\Api\Auctio\Entity\CollectionDay $day)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'ext123/lotcollectionday', ["headers"=>$requestHeader, "body"=>$day->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    public function updateDisplayDay(\AuctioCore\Api\Auctio\Entity\DisplayDay $day)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'ext123/lotdisplayday', ["headers"=>$requestHeader, "body"=>$day->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    public function updateLot(\AuctioCore\Api\Auctio\Entity\Lot $lot)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('POST', 'ext123/lot', ["headers"=>$requestHeader, "body"=>$lot->encode()]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate", "lastBidTime"]);
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

    public function updateLotMetaData(\AuctioCore\Api\Auctio\Entity\LotMetaData $lotMetaData)
    {
        // Check for current lot-metadata (because lot-metadata will be totally overwritten)
        $current = $this->getLotMetaData($lotMetaData->id);

        // If current lot-metadata not available, create else update lot-metadata
        if ($current === false || empty($current)) {
            // Create lot-metadata
            return $this->createLotMetaData($lotMetaData);
        } else {
            // Update lot-metadata (merge current with new data)
            foreach ($current[0]->metadata AS $metaDataElement) {
                // Iterate new lot-metadata elements
                $exists = false;
                foreach ($lotMetaData->metadata AS $k => $newMetaDataElement) {
                    // Check if element exists in new lot-metadata
                    if ($newMetaDataElement->key == $metaDataElement->key) {
                        // Iterate translations of meta-data element (value)
                        $exists = true;
                        foreach (get_object_vars($metaDataElement->value) AS $key => $value) {
                            // Check if translation available in new lot-metadata element, else merge value
                            if (!isset($newMetaDataElement->value->$key)) {
                                $lotMetaData->metadata[$k]->value->$key = $value;
                            }
                        }
                    }
                }

                // Add element if not exists
                if ($exists === false) {
                    $lotMetaData->metadata[] = new \AuctioCore\Api\Auctio\Entity\MetaData(["key"=>$metaDataElement->key, "value"=>$metaDataElement->value]);
                }
            }

            // Prepare request
            $requestHeader = $this->clientHeaders;

            // Execute request
            $result = $this->client->request('PUT', 'lot-metadata', ["headers"=>$requestHeader, "body"=>$lotMetaData->encode()]);
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

    public function updateCategory($categoryId, \AuctioCore\Api\Auctio\Entity\Category $category)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'categories/' . $categoryId, ["headers"=>$requestHeader, "body"=>$category->encode()]);
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

    public function deleteCollectionDay($dayId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('DELETE', 'ext123/lotcollectionday/' . $dayId, ["headers"=>$requestHeader]);
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

    public function deleteDisplayDay($dayId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('DELETE', 'ext123/lotdisplayday/' . $dayId, ["headers"=>$requestHeader]);
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

    public function deleteLotMedia($lotId, $imageName)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('DELETE', 'ext123/lotmedia/' . $lotId . '/' . $imageName, ["headers"=>$requestHeader]);
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
                $response = $this->convertDates($response, ["creationDate", "startDate", "endDate"]);
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

    /**
     * Get main/sub-categories in specific auction
     *
     * @param integer $auctionId
     * @param string $language
     * @return bool|object
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function getAuctionCategories($auctionId, $language = "nl")
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/" . $language . "/lotcategories/true/true", ["headers"=>$requestHeader]);
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

    public function getAuctionCollectionDays($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/lotcollectiondays", ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                foreach ($response AS $k => $v) {
                    $response[$k] = $this->convertDates($v, ["startDate", "endDate"]);
                }
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

    public function getAuctionCollectionDay($dayId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lotcollectionday/' . $dayId, ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    public function getAuctionDisplayDays($auctionId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/lotdisplaydays", ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                foreach ($response AS $k => $v) {
                    $response[$k] = $this->convertDates($v, ["startDate", "endDate"]);
                }
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

    public function getAuctionDisplayDay($dayId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lotdisplayday/' . $dayId, ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate"]);
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

    public function getAuctionLocation($locationId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/location/' . $locationId . "", ["headers"=>$requestHeader]);
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

    public function getAuctionMainCategories($auctionId, $showEmptySubCategories = "true")
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auction/' . $auctionId . "/lotmaincategories/" . $showEmptySubCategories, ["headers"=>$requestHeader]);
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

    public function getAuctionSubCategories($categoryId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lotmaincategory/' . $categoryId . "/lotsubcategories", ["headers"=>$requestHeader]);
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

    public function getAuctionsByCurrentPaged($pageSize = 25, $pageNumber = 1)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auctions/bycurrent/paged?page=' . $pageNumber . '&pageSize=' . $pageSize, ["headers"=>$requestHeader]);
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

    public function getAuctionsByFuturePaged($pageSize = 25, $pageNumber = 1)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/auctions/byfuture/paged?page=' . $pageNumber . '&pageSize=' . $pageSize, ["headers"=>$requestHeader]);
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

    public function getLocation($locationId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/location/' . $locationId, ["headers"=>$requestHeader]);
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
                $response = $this->convertDates($response, ["startDate", "endDate", "lastBidTime"]);
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

    public function getLotByNumber($auctionId, $lotNumber, $lotNumberAddition = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lot/' . $auctionId . '/' . $lotNumber . $lotNumberAddition . '/lotbynumber', ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate", "lastBidTime"]);
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

    public function getLotBidData($lotId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lot/' . $lotId . '/biddata', ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                $response = $this->convertDates($response, ["startDate", "endDate", "lastBidTime"]);
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

    public function getLotDetails($lotId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Implode multiple ids to string (comma-separated)
        if (is_array($lotId)) {
            $pageSize = count($lotId);
            $lotId = implode(",", $lotId);
        } else {
            $pageSize = 1;
        }

        // Execute request
        $result = $this->client->request('GET', 'lot-details/?ids=' . $lotId . '&pageNumber=1&pageSize=' . $pageSize, ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                if (!empty($response->restLotDetailsList)) {
                    foreach ($response->restLotDetailsList AS $k => $v) {
                        $response->restLotDetailsList[$k] = $this->convertDates($v, ["endDate"]);
                    }
                }
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

    public function getLotMedia($lotId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lot/' . $lotId . '/media', ["headers"=>$requestHeader]);
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

    public function getLotMediaFiles($lotId)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lotmedia/' . $lotId, ["headers"=>$requestHeader]);
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

    public function getLotMetaData($lotId, $language = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        if (!empty($language)) {
            $requestHeader['Accept-language'] = $language;
        }

        // Implode multiple ids to string (comma-separated)
        if (is_array($lotId)) $lotId = implode(",", $lotId);

        // Execute request
        $result = $this->client->request('GET', 'lot-metadata?ids=' . $lotId, ["headers"=>$requestHeader]);
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

    /**
     * Get (all) lots by auction-id, for example indexedBy by lot-number (by default sequantial numeric key)
     *
     * @param int $auctionId
     * @param string $indexedBy
     * @return array
     */
    public function getLotsByAuction($auctionId, $indexedBy = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Set page-config
        $pageSize = 100;
        $pageNumber = 1;
        $pages = 1;

        // Execute request (loop for all lots)
        $error = false;
        while ($error === false && $pages >= $pageNumber) {
            $result = $this->client->request('GET', 'ext123/lots/byauction/' . $auctionId . '/' . $pageSize . '/' . $pageNumber . '?enddate=ASC', ["headers"=>$requestHeader]);
            if ($result->getStatusCode() == 200) {
                $response = json_decode((string) $result->getBody());

                // Reset total pages of auction
                $pages = (int) ceil($response->totalLotCount / $response->pageSize);
                $pageNumber++;

                // Merge lots of different calls (because of while-loop)
                if (strtolower($indexedBy) == 'lotid') {
                    // Set lots-array
                    if (!isset($lots)) $lots = [];
                    // Reset index of lots-array to lot-id
                    foreach ($response->lots AS $lot) {
                        $lots[$lot->id] = $lot;
                    }
                } elseif (strtolower($indexedBy) == 'lotnumber') {
                    // Set lots-array
                    if (!isset($lots)) $lots = [];
                    // Reset index of lots-array to lot-number
                    foreach ($response->lots AS $lot) {
                        $lots[$lot->fullNumber] = $lot;
                    }
                } else {
                    // Merge lots
                    $lots = (isset($lots) && !empty($lots)) ? array_merge($lots, $response->lots) : $response->lots;
                }

                // Set lots to response
                $response->lots = $lots;
            } else {
                $response = json_decode((string) $result->getBody());

                // Set error, break while-loop
                $error = true;
            }
        }

        if ($error === false) {
            // Return
            if (!isset($response->errors)) {
                return $response;
            } else {
                $this->setErrorData($response);
                $this->setMessages($response->errors);
                return false;
            }
        } else {
            $this->setErrorData($response);
            $this->setMessages([$result->getStatusCode() . ": " . $result->getReasonPhrase()]);
            return false;
        }
    }

    public function getLotsByAuctionPaged($auctionId, $pageSize = 25, $pageNumber = 1)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('GET', 'ext123/lots/byauction/' . $auctionId . '/' . $pageSize . '/' . $pageNumber . '/', ["headers"=>$requestHeader]);
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

    public function getMainCategory($categoryId, $language = null, $new = true)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        if (!empty($language)) {
            $requestHeader['Accept-language'] = $language;
        }

        // Execute request
        if ($new === false) $result = $this->client->request('GET', 'ext123/lotmaincategory/' . $categoryId, ["headers"=>$requestHeader]);
        else $result = $this->client->request('GET', 'categories/' . $categoryId, ["headers"=>$requestHeader]);
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

    public function getSubCategory($subCategoryId, $language = null, $new = true)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        if (!empty($language)) {
            $requestHeader['Accept-language'] = $language;
        }

        // Execute request
        if ($new === false) $result = $this->client->request('GET', 'ext123/lotsubcategory/' . $subCategoryId, ["headers"=>$requestHeader]);
        else {
            //$result = $this->client->request('GET', 'sub-categories/' . $subCategoryId, ["headers"=>$requestHeader]);
            $result = $this->client->request('GET', 'standard-sub-categories/' . $subCategoryId, ["headers"=>$requestHeader]);
        }
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

    /**
     * Converting dates (from UTC to local timezone)
     *
     * @param object $object
     * @param array $attributes
     * @return object
     */
    protected function convertDates($object, $attributes = null)
    {
        // Iterate attributes for converting dates
        foreach ($attributes AS $attribute) {
            if (!empty($object->$attribute)) {
                $object->$attribute = (new \DateTime($object->$attribute))->setTimezone($this->tz);
            }
        }

        return $object;
    }
}