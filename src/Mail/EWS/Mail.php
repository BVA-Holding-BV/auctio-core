<?php

namespace AuctioCore\Mail\EWS;

use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\Request\CreateItemType;
use \jamesiarmes\PhpEws\Request\SendItemType;

use \jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;

use \jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use \jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;

use \jamesiarmes\PhpEws\Type\BodyType;
use \jamesiarmes\PhpEws\Type\EmailAddressType;
use \jamesiarmes\PhpEws\Type\ItemIdType;
use \jamesiarmes\PhpEws\Type\MessageType;
use \jamesiarmes\PhpEws\Type\SingleRecipientType;

class Mail
{

    private $client;
    private $sender;
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
    public function __construct($hostname, $username, $password, $senderMailAddress, $version = 'Exchange2010')
    {
        // Set client
        $this->client = new Client($hostname, $username, $password, $version);
        $this->sender = $senderMailAddress;

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

    public function send($mailRecipient, $subject = NULL, $content, $saveToFolder = true)
    {
        // Check input-data
        if (empty($mailRecipient)) {
            $this->setMessages("No recipient set");
            return false;
        } elseif (empty($subject)) {
            $this->setMessages("No subject set");
            return false;
        } elseif (empty($content)) {
            $this->setMessages("No content set");
            return false;
        }

        // Build the request,
        $request = new CreateItemType();
        $request->SaveItemToFolder = ($saveToFolder === false) ? false : true;
        $request->Items = new NonEmptyArrayOfAllItemsType();

        // Save the message, but do not send it
        $request->MessageDisposition = MessageDispositionType::SAVE_ONLY;

        // Create the message
        $message = new MessageType();
        $message->Subject = $subject;
        $message->ToRecipients = new ArrayOfRecipientsType();

        // Set the sender
        $message->From = new SingleRecipientType();
        $message->From->Mailbox = new EmailAddressType();
        $message->From->Mailbox->EmailAddress = $this->sender;

        // Set the recipient
        $recipient = new EmailAddressType();
        $recipient->EmailAddress = $mailRecipient;
        $message->ToRecipients->Mailbox[] = $recipient;

        // Set the message body
        $message->Body = new BodyType();
        $message->Body->BodyType = BodyTypeType::TEXT;
        $message->Body->_ = $content;

        // Add the message to the request
        $request->Items->Message[] = $message;
        $response = $this->client->CreateItem($request);

        // Iterate over the results
        $response_messages = $response->ResponseMessages->CreateItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $this->setMessages($response_message->ResponseCode . ": " . $response_message->MessageText);
                return false;
            }

            // Iterate over the created messages, printing the id for each.
            foreach ($response_message->Items->Message as $item) {
                return $this->sendMail($item->ItemId->Id, $item->ItemId->ChangeKey);
            }
        }
    }

    private function sendMail($messageId, $changeKey)
    {
        // Check input-data
        if (empty($messageId)) {
            $this->setMessages("No message-id set");
            return false;
        } elseif (empty($changeKey)) {
            $this->setMessages("No change-key set");
            return false;
        }

        // Build the request
        $request = new SendItemType();
        $request->SaveItemToFolder = true;
        $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();

        // Add the message to the request
        $item = new ItemIdType();
        $item->Id = $messageId;
        $item->ChangeKey = $changeKey;
        $request->ItemIds->ItemId[] = $item;

        // Sent message to
        $response = $this->client->SendItem($request);

        // Iterate over the results
        $response_messages = $response->ResponseMessages->SendItemResponseMessage;
        foreach ($response_messages as $response_message) {
            // Make sure the request succeeded.
            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                $this->setMessages($response_message->ResponseCode . ": " . $response_message->MessageText);
                return false;
            }
            return true;
        }
    }
}