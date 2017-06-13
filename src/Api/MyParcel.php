<?php

namespace AuctioCore\Api;

class MyParcel
{

    private $allowedCountryCodes;
    private $carrierId = 1; // PostNL
    private $client;
    private $clientHeaders;
    private $parcelOptions = [];
    private $recipient = [];

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $apiKey
     * @param array $allowedCountryCodes
     */
    public function __construct($hostname, $apiKey, $allowedCountryCodes = ["NL","BE"])
    {
        $this->allowedCountryCodes = $allowedCountryCodes;

        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname]);

        // Set default header for client-requests
        $this->clientHeaders = [
            'Authorization' => 'Basic ' . base64_encode($apiKey),
            'Content-Type' => 'application/json',
        ];
    }

    public function setParcelOptions($options)
    {
        // Iterate options
        foreach ($options AS $key => $value) {
            $this->setParcelOption($key, $value);
        }
    }

    public function setParcelOption($key, $value)
    {
        // Set option
        $this->parcelOptions[$key] = $value;
    }

    public function unsetParcelOptions()
    {
        $this->parcelOptions = [];
    }

    public function setRecipient($recipient)
    {
        // Iterate recipient-data
        foreach ($recipient AS $key => $value) {
            $this->setRecipientData($key, $value);
        }
    }

    public function setRecipientData($key, $value)
    {
        // Set recipient
        $this->recipient[$key] = utf8_encode(trim($value));
    }

    public function unsetRecipient()
    {
        $this->recipient = [];
    }

    /**
     * Create shipment
     *
     * @return array
     */
    public function createShipment()
    {
        // Set shipment-body
        $body = [
            "data" => [
                "shipments" => [
                    [
                        "recipient" => $this->recipient,
                        "options" => $this->parcelOptions,
                        "carrier" => $this->carrierId
                    ]
                ]
            ]
        ];

        $requestHeader = $this->clientHeaders;
        $requestHeader['Content-Type'] = "application/vnd.shipment+json";
        $result = $this->client->request('POST', '/shipments/', ["headers"=>$requestHeader, "body"=>json_encode($body)]);
        $response = json_decode((string) $result->getBody());
        if (!isset($response->errors)) {
            // Unset parcel-options and recipient
            $this->unsetParcelOptions();
            $this->unsetRecipient();

            // Return
            return ["error"=>false, "message"=>"Ok", "data"=>["shipmentId" => $response->data->ids[0]->id]];
        } else {
            // Return
            return ["error"=>true, "message"=>$response->errors, "data"=>[]];
        }
    }

    /**
     * Get shipment-label (download)
     *
     * @param int|array $id
     * @param string $localFilename
     * @return boolean
     */
    public function getShipmentLabel($id, $localFilename = null)
    {
        // Set uri, depending on download single or multiple labels (in one PDF-file)
        if (is_array($id)) {
            // Temporary fix (for caching), get shipment-labels in portion of 6
            if (count($id) > 6) {
                $tmpIds = [];
                foreach ($id AS $value) {
                    // Set temporary-id
                    $tmpIds[] = $value;

                    // Get shipment-labels (for caching)
                    if (count($tmpIds) == 6) {
                        $this->getShipmentLabel($tmpIds);
                        // Reset temporary-ids
                        $tmpIds = [];
                    }
                }

                // Get shipment-labels (for caching)
                if (count($tmpIds) > 0) {
                    $this->getShipmentLabel($tmpIds);
                }
            }
            $uri = '/shipment_labels/' . implode(";", $id);
        } else {
            $uri = '/shipment_labels/' . $id;
        }

        // Get PDF-file
        $result = $this->client->request('GET', $uri, ["headers"=>$this->clientHeaders]);
        if ($result->getStatusCode() == 200) {
            if (!empty($localFilename)) {
                // Save PDF-file (local)
                $response = (string)$result->getBody();
                $fp = fopen($localFilename, "w+");
                fwrite($fp, $response);
                fclose($fp);
            }

            // Return
            return true;
        } else {
            // Return
            return ['code'=>$result->getStatusCode(), 'reason'=>$result->getReasonPhrase()];
        }
    }

    /**
     * Get shipment-details
     *
     * @param $id
     * @return array|mixed
     */
    public function getShipment($id)
    {
        // Get shipment-details
        $result = $this->client->request('GET', '/shipments/' . $id, ["headers"=>$this->clientHeaders]);
        if ($result->getStatusCode() == 200) {
            $response = json_decode((string) $result->getBody());
            $result = current($response->data->shipments);

            // Return
            return $result;
        } else {
            // Return
            return ['code'=>$result->getStatusCode(), 'reason'=>$result->getReasonPhrase()];
        }
    }

    /**
     * Validate address according MyParcel-constraints
     *
     * @param string $person
     * @param string $street
     * @param integer $number
     * @param string $numberSuffix
     * @param string $postalCode
     * @param string $city
     * @param string $countryCode
     * @return boolean|array
     */
    public function validateAddress($person, $street, $number, $numberSuffix, $postalCode, $city, $countryCode)
    {
        $errors = array();
        if (empty($person)) $errors['person'] = "Value is required";
        if (empty($street)) $errors['street'] = "Value is required";
        if (empty($number)) $errors['number'] = "Value is required";
        if (empty($postalCode)) $errors['postalCode'] = "Value is required";
        if (empty($city)) $errors['city'] = "Value is required";
        if (empty($countryCode)) $errors['countryCode'] = "Value is required";

        // Check if person is max 50 characters
        if (!empty($person) && strlen($person) > 50) {
            $errors['person'] = "Value is not allowed (max 50 characters)";
        }

        // Check if number is only numeric
        if (!empty($number) && preg_match("/[^0-9]/", $number)) {
            $errors['number'] = "Value is not allowed (only numbers)";
        }

        // Check if numberSuffix is not equal to street
        if (!empty($numberSuffix) && strtolower($numberSuffix) == strtolower($street)) {
            $errors['numberSuffix'] = "Value is not valid (same as street)";
        }

        // Check if numberSuffix is not equal to number
        if (!empty($numberSuffix) && strtolower($numberSuffix) == strtolower($number)) {
            $errors['numberSuffix'] = "Value is not valid (same as number)";
        }

        // Check if numberSuffix is max 4 characters
        if (!empty($numberSuffix) && strlen($numberSuffix) > 4) {
            $errors['numberSuffix'] = "Value is not allowed (max 4 characters)";
        }

        // Check postalCode
        if (!empty($postalCode) && !empty($countryCode)) {
            // Set regular expressions for postalCode per countryCode
            $regularExpr = [
                "NL"=>"/[1-9][0-9]{3}[A-Z]{2}/",
                "BE"=>"/[1-9]{1}[0-9]{3}/"
            ];

            // Check if postalCode is valid
            if (array_key_exists($countryCode, $regularExpr) && !preg_match($regularExpr[$countryCode], $postalCode)) {
                $errors['postalCode'] = "Value is not valid";
            }
        }

        // Check if countryCode is allowed for sending
        if (!empty($countryCode) && !in_array($countryCode, $this->allowedCountryCodes)) {
            $errors['countryCode'] = "Value is not allowed (" . implode(", ", $this->allowedCountryCodes) . ")";
        }

        if (empty($errors)) return true;
        else return $errors;
    }

}