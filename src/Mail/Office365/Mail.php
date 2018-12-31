<?php
namespace AuctioCore\Mail\Office365;

use \AuctioCore\Api\Microsoft;

class Mail
{

    private $office365Api;
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

            // Set Office365 API
            $this->office365Api = new \AuctioCore\Api\Microsoft\Office365Api($hostname, $this->token);
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
     * @param string|array $recipients
     * @param string $subject
     * @param string $content
     * @param string $bodyType
     * @param boolean $saveToFolder
     * @param boolean|array $attachments
     * @return boolean
     */
    public function send($recipients, $subject = NULL, $content, $bodyType = 'Text', $saveToFolder = true, $attachments = false)
    {
        // Set option save message to folder
        if ($saveToFolder !== true) {
            $saveToFolder = false;
        }

        // Send mail
        $result = $this->office365Api->sendMail($recipients, $subject, $content, $bodyType, $saveToFolder, $attachments);
        if ($result === false) {
            $this->setMessages($this->office365Api->getMessages());
            $this->setErrorData($this->office365Api->getErrorData());
            return false;
        } else {
            return true;
        }
    }
}