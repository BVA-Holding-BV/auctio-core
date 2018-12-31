<?php
/**
 * API-information: https://docs.microsoft.com/en-us/previous-versions/office/office-365-api/api/version-2.0/mail-rest-operations
 */
namespace AuctioCore\Api\Microsoft;

class Office365Api
{

    private $client;
    private $token;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $clientId
     * @param string $clientSecret
     * @param boolean $debug
     */
    public function __construct($hostname, $token, $debug = false)
    {
        // Set client
        $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false, 'debug'=>$debug]);

        // Set token
        $this->token = $token;

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
     * Create/send email by Office365
     *
     * @param string|array $recipients
     * @param string $subject
     * @param string $content
     * @param string $bodyType
     * @param boolean $saveToFolder
     * @param boolean|array $attachments
     * @return boolean|object
     */
    public function sendMail($recipients, $subject, $content, $bodyType = 'Text', $saveToFolder = true, $attachments = false)
    {
        // Check input-data
        if (empty($this->token)) {
            $this->addMessage("No token available");
            return false;
        } elseif (empty($recipients)) {
            $this->setMessages("No recipient set");
            return false;
        } elseif (empty($subject)) {
            $this->setMessages("No subject set");
            return false;
        } elseif (empty($content)) {
            $this->setMessages("No content set");
            return false;
        } elseif (!in_array(strtolower($bodyType), ['html','text'])) {
            $this->setMessages("Invalid body-type set");
            return false;
        }

        // Build the request
        $parameters = [
            "Message" => [
                "Subject" => $subject,
                "Body" => [
                    "ContentType" => $bodyType,
                    "Content" => $content,
                ],
                "ToRecipients" => [],
                "Attachments" => [],
            ],
        ];

        // Set option save message to folder
        if ($saveToFolder === true) {
            $parameters["SaveToSentItems"] = "true";
        } else {
            $parameters["SaveToSentItems"] = "false";
        }

        // Set recipients
        if (!is_array($recipients)) {
            $parameters["Message"]["ToRecipients"][] = [
                "EmailAddress" => [
                    "Address" => $recipients
                ]
            ];
        } else {
            foreach ($recipients AS $recipient) {
                $parameters["Message"]["ToRecipients"][] = [
                    "EmailAddress" => [
                        "Address" => $recipient
                    ]
                ];
            }
        }

        // Set attachments (if available)
        if (!empty($attachments)) {
            foreach ($attachments AS $attachment) {
                // Get binary content of attachment
                $fp = fopen($attachment['path'], "r");
                $contents = fread($fp, filesize($attachment['path']));
                fclose($fp);

                // Add attachment to message
                $parameters["Message"]["Attachments"][] = [
                    "@odata.type" => "#Microsoft.OutlookServices.FileAttachment",
                    "Name" => $attachment['name'],
                    "ContentBytes" => base64_encode($contents),
                ];
            }
        }

        // Send mail
        $result = $this->client->request('POST', 'v2.0/me/sendmail', ["headers"=>["Authorization"=>$this->token, "Content-Type"=>"application/json"], "body"=>json_encode($parameters)]);
        $response = json_decode((string) $result->getBody());
        if ($result->getStatusCode() == 200 || $result->getStatusCode() == 202) {
            return true;
        } else {
            $this->setMessages($response->error->code . ": " . $response->error->message);
            return false;
        }
    }

}