<?php
/**
 * API-information: https://docs.microsoft.com/en-us/previous-versions/office/office-365-api/api/version-2.0/mail-rest-operations
 */
namespace AuctioCore\Mail\Office365;

use \AuctioCore\Api\Microsoft;

class Mail
{

    private $token;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $tenant
     * @param string $clientId
     * @param string $clientSecret
     * @param string $username
     * @param string $password
     */
    public function __construct($hostname, $tenant, $clientId, $clientSecret, $username, $password)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Get token by Azure AD
        $azureApi = new Microsoft\AzureApi('https://login.microsoftonline.com/' . $tenant . '/', $clientId, $clientSecret, "https://outlook.office365.com");
        $token = $azureApi->authorize($username, $password);
        if ($token !== false) {
            // Set token
            $this->token = $token->token_type . " " . $token->access_token;

            // Set mail-client
            $this->client = new \GuzzleHttp\Client(['base_uri'=>$hostname, 'http_errors'=>false]);
        } else {
            $this->setMessages($azureApi->getMessages());
            $this->setErrorData($azureApi->getErrorData());
        }
    }

    /**
     * Set error-data
     *
     * @param $data
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
     * @param string|array $mailRecipients
     * @param string $subject
     * @param string $content
     * @param string $bodyType
     * @param boolean $saveToFolder
     * @param boolean|array $attachments
     * @return boolean
     */
    public function send($mailRecipients, $subject = NULL, $content, $bodyType = 'Text', $saveToFolder = true, $attachments = false)
    {
        // Check input-data
        if (empty($this->token)) {
            $this->addMessage("No token available");
            return false;
        } elseif (empty($mailRecipients)) {
            $this->setMessages("No recipient set");
            return false;
        } elseif (empty($subject)) {
            $this->setMessages("No subject set");
            return false;
        } elseif (empty($content)) {
            $this->setMessages("No content set");
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
        if (!is_array($mailRecipients)) {
            $parameters["Message"]["ToRecipients"][] = [
                "EmailAddress" => [
                    "Address" => $mailRecipients
                ]
            ];
        } else {
            foreach ($mailRecipients AS $mailRecipient) {
                $parameters["Message"]["ToRecipients"][] = [
                    "EmailAddress" => [
                        "Address" => $mailRecipient
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