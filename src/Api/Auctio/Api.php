<?php
/**
 * API-information: https://api.bva-auctions.com/api/docs/
 */
namespace AuctioCore\Api\Auctio;

use AuctioCore\Api\Auctio\Entity\Auction;
use AuctioCore\Api\Auctio\Entity\Category;
use AuctioCore\Api\Auctio\Entity\CollectionDay;
use AuctioCore\Api\Auctio\Entity\DisplayDay;
use AuctioCore\Api\Auctio\Entity\Location;
use AuctioCore\Api\Auctio\Entity\Lot;
use AuctioCore\Api\Auctio\Entity\LotMetaData;
use AuctioCore\Api\Auctio\Entity\MetaData;
use DateTime;
use DateTimeZone;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\HandlerStack;
use GuzzleRetry\GuzzleRetryMiddleware;

class Api
{

    private array $errorData;
    private Client $client;
    private array $clientHeaders;
    private array $messages;
    private array $token;
    private DateTimeZone $tz;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param null $username
     * @param null $password
     * @param null $userAgent
     * @param null $token
     * @param boolean $debug
     */
    public function __construct(string $hostname, $username = null, $password = null, $userAgent = null, $token = null, $debug = false)
    {
        // Set time-zone for converting "back" from UTC
        $this->tz = new DateTimeZone('Europe/Amsterdam');

        $stack = HandlerStack::create();
        $stack->push(GuzzleRetryMiddleware::factory([
            'max_retry_attempts' => 5,
        ]));

        // Set client
        $this->client = new Client(['base_uri'=>$hostname, 'http_errors'=>false, 'handler'=>$stack, 'debug'=>$debug]);

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
     * @param array|string $data
     */
    public function setErrorData($data)
    {
        if (!is_array($data)) $data = [$data];
        $this->errorData = $data;
    }

    /**
     * Get error-data
     *
     * @return array
     */
    public function getErrorData(): array
    {
        return $this->errorData;
    }

    /**
     * Set error-message
     *
     * @param array|string $messages
     */
    public function setMessages($messages)
    {
        if (!is_array($messages)) $messages = [$messages];
        $this->messages = $messages;
    }

    /**
     * Add error-message
     *
     * @param array|string $message
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
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Set token
     *
     * @param array $data
     */
    public function setToken(array $data)
    {
        $this->token = $data;
    }

    /**
     * Get token
     *
     * @return array
     */
    public function getToken(): array
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
     * @throws GuzzleException
     */
    public function login(string $username, string $password, $retry = true): bool
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
     * @throws GuzzleException
     */
    public function logout(): bool
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
     * @throws GuzzleException
     */
    public function createAuction(Auction $auction)
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
     * @throws GuzzleException
     */
    public function createCollectionDay(CollectionDay $day)
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
     * @throws GuzzleException
     */
    public function createDisplayDay(DisplayDay $day)
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
     * @throws GuzzleException
     */
    public function createLocation(Location $location)
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
     * @throws GuzzleException
     */
    public function createLot(Lot $lot)
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
     * @param null $uploadFile
     * @return bool|object
     * @throws GuzzleException
     */
    public function createLotMedia(int $lotId, int $lotSequence, string $localFilename, int $imageSequence, $uploadFile = null)
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

    private function createLotMetaData(LotMetaData $lotMetaData)
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

    public function updateAuction(Auction $auction)
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

    public function updateCollectionDay(CollectionDay $day)
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

    public function updateDisplayDay(DisplayDay $day)
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

    public function updateLot(Lot $lot)
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

    public function updateLotMetaData(LotMetaData $lotMetaData)
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
                    $lotMetaData->metadata[] = new MetaData(["key"=>$metaDataElement->key, "value"=>$metaDataElement->value]);
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

    public function updateCategory($categoryId, Category $category)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Execute request
        $result = $this->client->request('PUT', 'categories/' . $categoryId, ["headers"=>$requestHeader, "body"=>$category->encode()]);
        if (in_array($result->getStatusCode(), [200,201,204])) {
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
     * @throws GuzzleException
     */
    public function getAuctionCategories($auctionId, $language = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        if (!empty($language)) {
            $requestHeader['Accept-language'] = $language;
        }

        // Execute request
        $result = $this->client->request('GET', 'auction-categories?auctionId=' . $auctionId, ["headers"=>$requestHeader]);
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

    public function getLotCategoryAttributes($lotIds, $language = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;
        if (!empty($language)) {
            $requestHeader['Accept-language'] = $language;
        } else {
            $requestHeader['Accept-language'] = "nl";
        }

        // Chunk list into chunks of max 50 elements
        if (!is_array($lotIds)) $lotIds = [$lotIds];
        $lotChunks = array_chunk($lotIds, 50);

        // Iterate lot-chunks
        $output = [];
        foreach ($lotChunks AS $lotChunk) {
            // Implode multiple ids to string (comma-separated)
            $lotString = implode(",", $lotChunk);

            // Execute request
            $result = $this->client->request('GET', 'lot-category-attributes?lotIds=' . $lotString, ["headers"=>$requestHeader]);
            if ($result->getStatusCode() == 200) {
                $response = json_decode((string) $result->getBody(), true);
                if (empty($response)) continue;

                // Return
                if (!isset($response['errors'])) {
                    // Iterate lots
                    foreach ($lotChunk AS $lotId) {
                        // Initialize
                        $output[$lotId] = $response[$lotId];
                        // Reset attributes (use attribute-id as key)
                        foreach ($output[$lotId]['attributes'] AS $k => $attribute) {
                            $output[$lotId]['attributes'][$attribute['attribute']['id']] = $attribute;
                            unset($output[$lotId]['attributes'][$k]);
                        }
                    }
                } else {
                    $this->setErrorData($response);
                    $this->setMessages($response['errors']);
                    return false;
                }
            } else {
                $response = json_decode((string) $result->getBody());
                $this->setErrorData($response);
                $this->setMessages([$result->getStatusCode() . ": " . $result->getReasonPhrase()]);
                return false;
            }
        }

        // Return
        return $output;
    }

    public function getLotDetails($lotIds)
    {
        // Chunk lot-id list into chunks of max 150 elements (maximum of single page-response endpoint)
        if (is_array($lotIds)) {
            $lotChunks = array_chunk($lotIds, 150);
        } else {
            $lotChunks = [$lotIds];
        }

        // Iterate lot-chunks
        $output = null;
        foreach ($lotChunks AS $lotChunk) {
            $result = $this->getLotDetailsPaged($lotChunk);
            if (!empty($output)) {
                $output->restLotDetailsList = array_merge($result->restLotDetailsList, $output->restLotDetailsList);
            } else {
                $output = $result;
            }
        }

        // Return
        return $output;
    }

    private function getLotDetailsPaged($lotIds, $pageNumber = 1, $pagedResponse = null)
    {
        // Prepare request
        $requestHeader = $this->clientHeaders;

        // Implode multiple ids to string (comma-separated)
        if (is_array($lotIds)) {
            $pageSize = count($lotIds);
            $ids = implode(",", $lotIds);
        } else {
            $pageSize = 1;
            $ids = $lotIds;
        }

        // Execute request
        $result = $this->client->request('GET', 'lot-details/?ids=' . $ids . '&pageNumber=' . $pageNumber . '&pageSize=' . $pageSize, ["headers"=>$requestHeader]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());

            // Return
            if (!isset($response->errors)) {
                if (!empty($response->restLotDetailsList)) {
                    foreach ($response->restLotDetailsList AS $k => $v) {
                        $response->restLotDetailsList[$k] = $this->convertDates($v, ["endDate"]);
                    }

                    if (!empty($pagedResponse))
                        $response->restLotDetailsList = array_merge($response->restLotDetailsList, $pagedResponse->restLotDetailsList);

                    if ($response->paginator->hasNext === true) {
                        $pageNumber++;
                        return $this->getLotDetailsPaged($lotIds, $pageNumber, $response);
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
        } else {
            $requestHeader['Accept-language'] = "none";
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
     * @return false|array
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
                        $lot = $this->convertDates($lot, ["startDate", "endDate"]);
                        $lots[$lot->id] = $lot;
                    }
                } elseif (strtolower($indexedBy) == 'lotnumber') {
                    // Set lots-array
                    if (!isset($lots)) $lots = [];
                    // Reset index of lots-array to lot-number
                    foreach ($response->lots AS $lot) {
                        $lot = $this->convertDates($lot, ["startDate", "endDate"]);
                        $lots[$lot->fullNumber] = $lot;
                    }
                } else {
                    // Convert lot-dates
                    foreach ($response->lots AS $k => $lot) {
                        $lot = $this->convertDates($lot, ["startDate", "endDate"]);
                        $response->lots[$k] = $lot;
                    }

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
        else $result = $this->client->request('GET', 'sub-categories/' . $subCategoryId, ["headers"=>$requestHeader]);
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
                // Check if date in format 2018-01-01T00:00:00.000+0000
                if (!is_numeric($object->$attribute)) {
                    $object->$attribute = (new DateTime($object->$attribute))->setTimezone($this->tz);
                }
                // Check if date in format 1514782800000 (microseconds since Unix Epoch)
                elseif (is_numeric($object->$attribute) && strlen($object->$attribute) >= 10) {
                    $object->$attribute = (new DateTime('@' .($object->$attribute / 1000)))->setTimezone($this->tz);
                }
                // Check if date in format 1514782800 (seconds since Unix Epoch)
                elseif (is_numeric($object->$attribute) && strlen($object->$attribute) <= 10) {
                    $object->$attribute = (new DateTime('@' .$object->$attribute))->setTimezone($this->tz);
                }
            }
        }

        return $object;
    }
}