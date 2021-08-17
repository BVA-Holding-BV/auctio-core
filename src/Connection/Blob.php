<?php
/**
 * API-information: http://azure.github.io/azure-storage-php/
 */
namespace AuctioCore\Connection;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;

class Blob
{

    private BlobRestProxy $client;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $connectionString
     * @param boolean $debug
     */
    public function __construct(string $connectionString, $debug = false)
    {
        // Set client
        $this->client = BlobRestProxy::createBlobService($connectionString);

        // Set error-messages
        $this->messages = [];
        $this->errorData = [];
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
     * Delete (blob) file
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function delete(string $remoteFileName): bool
    {
        return false;
    }

    /**
     * Check if (blob) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists(string $remoteFileName): bool
    {
        return false;
    }

    /**
     * Get list of files
     *
     * @param string $path
     * @param bool $recursive
     * @return array|false|mixed
     */
    public function getFiles($path = null, $recursive = false)
    {
        return false;
    }

    /**
     * Move/rename (blob) file
     *
     * @param string $currentFileName
     * @param string $newFileName
     * @return boolean
     */
    public function move(string $currentFileName, string $newFileName): bool
    {
        return false;
    }

    /**
     * Upload file
     *
     * @param string $remoteFileName
     * @param string $data
     * @return boolean
     */
    public function upload(string $remoteFileName, string $data): bool
    {
        return false;
    }
}