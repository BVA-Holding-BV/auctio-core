<?php
/**
 * API-information: http://azure.github.io/azure-storage-php/
 */
namespace AuctioCore\Connection;

use MicrosoftAzure\Storage\Blob\BlobRestProxy;
use MicrosoftAzure\Storage\Blob\Models\CreateBlockBlobOptions;
use MicrosoftAzure\Storage\Blob\Models\GetBlobPropertiesResult;
use MicrosoftAzure\Storage\Blob\Models\GetContainerPropertiesResult;

class BlobStorage
{

    private BlobRestProxy $client;
    private string $container;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $accountName
     * @param string $accountKey
     * @param string $container
     * @param boolean $debug
     */
    public function __construct(string $accountName, string $accountKey, string $container, $debug = false)
    {
        // Set client
        $connectionString = "DefaultEndpointsProtocol=https;AccountName=$accountName;AccountKey=$accountKey;EndpointSuffix=core.windows.net";
        $this->client = BlobRestProxy::createBlobService($connectionString);
        $this->container = $container;

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
        try {
            $this->client->deleteBlob($this->container, $remoteFileName);
        } catch (\Exception $e) {
            $this->addMessage("Failed to delete file $remoteFileName: " . $e->getMessage());
            return false;
        }

        // Return
        return true;
    }

    /**
     * Check if (blob) container exists
     *
     * @return bool
     */
    public function containerExists(): bool
    {
        $result = false;

        try {
            $containerProperties = $this->client->getContainerProperties($this->container);
            if ($containerProperties instanceof GetContainerPropertiesResult && !empty($containerProperties->getETag())) {
                $result = true;
            }
        } catch (\Exception $e) {
            $this->addMessage("Failed to retrieve container $this->container: " . $e->getMessage());
        }

        // Return
        return $result;
    }

    /**
     * Check if (blob) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists(string $remoteFileName): bool
    {
        $result = false;

        try {
            $blobProperties = $this->client->getBlobProperties($this->container, $remoteFileName);
            if ($blobProperties instanceof GetBlobPropertiesResult && !empty($blobProperties->getETag())) {
                $result = true;
            }
        } catch (\Exception $e) {
            $this->addMessage("Failed to retrieve file $remoteFileName: " . $e->getMessage());
        }

        // Return
        return $result;
    }

    /**
     * Upload file
     *
     * @param string $remoteFileName
     * @param string $data
     * @param string $contentType
     * @return boolean
     */
    public function upload(string $remoteFileName, string $data, string $contentType): bool
    {
        try {
            $options = new CreateBlockBlobOptions();
            $options->setContentType($contentType);

            $this->client->createBlockBlob($this->container, $remoteFileName, $data, $options);
        } catch (\Exception $e) {
            $this->addMessage("Failed to upload file $remoteFileName: " . $e->getMessage());
            return false;
        }

        // Return
        return true;
    }
}