<?php

namespace AuctioCore\Mail\EWS;

use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\Request\SendItemType;

use \jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;

use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;

use \jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;

use \jamesiarmes\PhpEws\Type\BodyType;
use \jamesiarmes\PhpEws\Type\EmailAddressType;
use \jamesiarmes\PhpEws\Type\MessageType;
use \jamesiarmes\PhpEws\Type\SingleRecipientType;

class Mail
{

    private $client;
    private $username;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $version
     */
    public function __construct($hostname, $username, $password, $version = 'Exchange2010')
    {
        // Set client
        $this->client = new Client($hostname, $username, $password, $version);
        $this->username = $username;

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

    public function send($recipient, $subject = NULL, $content, $saveToFolder = true)
    {
        // Check input-data
        if (empty($recipient)) {
            $this->setMessages("No recipient set");
            return false;
        } elseif (empty($subject)) {
            $this->setMessages("No subject set");
            return false;
        } elseif (empty($content)) {
            $this->setMessages("No content set");
            return false;
        }

        // Build the request.
        $request = new SendItemType();
        $request->SaveItemToFolder = ($saveToFolder === false) ? false : true;
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();

        // Create the message.
        $message = new MessageType();
        $message->Subject = $subject;
        $message->ToRecipients = new ArrayOfRecipientsType();

        // Set the sender.
        $senderMailAddress = str_ireplace("bva\\", "", $this->username) . "@bva-auctions.com";
        $message->From = new SingleRecipientType();
        $message->From->Mailbox = new EmailAddressType();
        $message->From->Mailbox->EmailAddress = $senderMailAddress;

        // Set the recipient.
        $recipient = new EmailAddressType();
        $recipient->EmailAddress = $recipient;
        $message->ToRecipients->Mailbox[] = $recipient;

        // Set the message body.
        $message->Body = new BodyType();
        $message->Body->BodyType = BodyTypeType::TEXT;
        $message->Body->_ = $content;

        // Add the message to the request.
        $request->Items->Message[] = $message;

        // Sent message
        $response = $this->client->SendItem($request);

        // Iterate over the results
        $response_messages = $response->ResponseMessages->SendItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $this->setMessages($response_message->ResponseCode . ": " . $response_message->MessageText);
                return false;
            }

            // Return true (message sent successfully)
            return true;
        }
    }

}