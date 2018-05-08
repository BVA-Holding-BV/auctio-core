<?php

namespace AuctioCore\Mail\EWS;

use \jamesiarmes\PhpEws\Client;
use \jamesiarmes\PhpEws\Request\CreateAttachmentType;
use \jamesiarmes\PhpEws\Request\CreateItemType;
use \jamesiarmes\PhpEws\Request\GetItemType;
use \jamesiarmes\PhpEws\Request\SendItemType;

use \jamesiarmes\PhpEws\ArrayType\ArrayOfRecipientsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAllItemsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfAttachmentsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfBaseItemIdsType;
use \jamesiarmes\PhpEws\ArrayType\NonEmptyArrayOfPathsToElementType;

use \jamesiarmes\PhpEws\Enumeration\BodyTypeType;
use \jamesiarmes\PhpEws\Enumeration\DefaultShapeNamesType;
use \jamesiarmes\PhpEws\Enumeration\MapiPropertyTypeType;
use \jamesiarmes\PhpEws\Enumeration\MessageDispositionType;
use \jamesiarmes\PhpEws\Enumeration\ResponseClassType;

use \jamesiarmes\PhpEws\Type\BodyType;
use \jamesiarmes\PhpEws\Type\EmailAddressType;
use \jamesiarmes\PhpEws\Type\FileAttachmentType;
use \jamesiarmes\PhpEws\Type\ItemIdType;
use \jamesiarmes\PhpEws\Type\ItemResponseShapeType;
use \jamesiarmes\PhpEws\Type\MessageType;
use \jamesiarmes\PhpEws\Type\PathToExtendedFieldType;
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
     * @param string $senderMailAddress
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
     * Create/send email by EWS
     *
     * @param string $mailRecipient
     * @param string $subject
     * @param string $content
     * @param boolean $saveToFolder
     * @param boolean|array $attachments
     * @return boolean
     */
    public function send($mailRecipient, $subject = NULL, $content, $bodyType = 'TEXT', $saveToFolder = true, $attachments = false)
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
        if (strtoupper($bodyType) == 'HTML') {
            $message->Body->BodyType = BodyTypeType::HTML;
            $message->Body->_ = $content;
        } else {
            $message->Body->BodyType = BodyTypeType::TEXT;
            // Set content (warning: Identifier must not be indented!); http://php.net/manual/en/language.types.string.php
            $message->Body->_ =
<<<BODY
$content 
BODY;
        }

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

            // Iterate over the created messages, sending for each.
            foreach ($response_message->Items->Message as $item) {
                // Add attachments (if available)
                if (is_array($attachments) && count($attachments) > 0) {
                    foreach ($attachments AS $attachmentFile) {
                        // Open file handlers.
                        $file = new \SplFileObject($attachmentFile);
                        $finfo = finfo_open();

                        // Build the request,
                        $request = new CreateAttachmentType();
                        $request->ParentItemId = new ItemIdType();
                        $request->ParentItemId->Id = $item->ItemId->Id;
                        $request->Attachments = new NonEmptyArrayOfAttachmentsType();

                        // Build the file attachment.
                        $attachment = new FileAttachmentType();
                        $attachment->Content = $file->openFile()->fread($file->getSize());
                        $attachment->Name = $file->getBasename();
                        $attachment->ContentType = finfo_file($finfo, $attachmentFile);
                        $request->Attachments->FileAttachment[] = $attachment;

                        // Add attachment to message
                        $response = $this->client->CreateAttachment($request);

                        // Iterate over the results, printing any error messages or the id of the new attachment.
                        $response_messages = $response->ResponseMessages->CreateAttachmentResponseMessage;
                        foreach ($response_messages as $response_message) {
                            // Make sure the request succeeded.
                            if ($response_message->ResponseClass != ResponseClassType::SUCCESS) {
                                $this->setMessages($response_message->ResponseCode . ": " . $response_message->MessageText);
                                return false;
                            } else {
                                // Build the request for getting updated message-item
                                $request = new GetItemType();
                                $request->ItemShape = new ItemResponseShapeType();
                                $request->ItemShape->BaseShape = DefaultShapeNamesType::ALL_PROPERTIES;
                                $request->ItemShape->AdditionalProperties = new NonEmptyArrayOfPathsToElementType();
                                $request->ItemIds = new NonEmptyArrayOfBaseItemIdsType();
                                // Add an extended property to the request
                                $property = new PathToExtendedFieldType();
                                $property->PropertyTag = '0x1081';
                                $property->PropertyType = MapiPropertyTypeType::INTEGER;
                                $request->ItemShape->AdditionalProperties->ExtendedFieldURI[] = $property;

                                $itemId = new ItemIdType();
                                $itemId->Id = $item->ItemId->Id;
                                $request->ItemIds->ItemId[] = $itemId;
                                $response = $this->client->GetItem($request);

                                // Get message-item
                                $item = $response->ResponseMessages->GetItemResponseMessage[0]->Items->Message[0];
                            }
                        }
                    }
                }

                // Send mail
                return $this->sendMail($item->ItemId->Id, $item->ItemId->ChangeKey);
            }
        }
    }

    /**
     * Send email by EWS (after creating message)
     *
     * @param string $messageId
     * @param string $changeKey
     * @return boolean
     */
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