<?php

namespace AuctioCore\Connection;

use phpseclib3\Crypt\RSA;

class SFTP
{
    private \phpseclib3\Net\SFTP $sftp;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param null $privateKeyPath
     * @param null $privateKeyPassword
     */
    public function __construct(string $hostname, string $username, string $password, $privateKeyPath = null, $privateKeyPassword = null)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Set sftp
        $this->sftp = new \phpseclib3\Net\SFTP($hostname);

        // Login by private-key
        if (!empty($privateKeyPath)) {
            // Set private-key
            $key = RSA::loadFormat('PKCS1', file_get_contents($privateKeyPath), $privateKeyPassword);

            // Login
            $res = $this->sftp->login($username, $key);
        } else {
            // Login
            $res = $this->sftp->login($username, $password);
        }

        // Return
        if (!$res) {
            $this->setMessages('Login failed');
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
     * Create a directory
     *
     * @param string $dir
     * @param null $mode
     * @param bool $recursive
     * @return bool
     */
    public function createDirectory(string $dir, $mode = null, $recursive = false): bool
    {
        return $this->sftp->mkdir($dir, $mode, $recursive);
    }

    /**
     * Delete (remote) file
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function delete(string $remoteFileName): bool
    {
        return $this->sftp->delete($remoteFileName);
    }

    /**
     * Download (remote) file
     *
     * @param string $remoteFileName
     * @param string $localFileName
     * @return bool|mixed|string
     */
    public function download(string $remoteFileName, string $localFileName)
    {
        return $this->sftp->get($remoteFileName, $localFileName);
    }

    /**
     * Check if (remote) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists(string $remoteFileName): bool
    {
        return $this->sftp->file_exists($remoteFileName);
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
        return $this->sftp->nlist($path, $recursive);
    }

    /**
     * Move/rename (remote) file
     *
     * @param string $currentFileName
     * @param string $newFileName
     * @return boolean
     */
    public function move(string $currentFileName, string $newFileName): bool
    {
        return $this->sftp->rename($currentFileName, $newFileName);
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
        return $this->sftp->put($remoteFileName, $data);
    }

}