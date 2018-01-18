<?php

namespace AuctioCore\Connection;

class SFTP
{
    private $sftp;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $privateKeyPath
     * @param string $privateKeyPassword
     */
    public function __construct($hostname, $username, $password, $privateKeyPath = null, $privateKeyPassword = null)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Set sftp
        $this->sftp = new \phpseclib\Net\SFTP($hostname);

        // Login by private-key
        if (!empty($privateKeyPath)) {
            // Set private-key
            $key = new \phpseclib\Crypt\RSA();
            $key->setPassword($privateKeyPassword);
            $key->loadKey(file_get_contents($privateKeyPath));

            // Login
            $res = $this->sftp->login($username, $key);
        } else {
            // Login
            $res = $this->sftp->login($username, $password);
        }

        // Return
        if (!$res) {
            $this->setMessages('Login failed');
            return false;
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
     * Delete (remote) file
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function delete($remoteFileName)
    {
        return $this->sftp->delete($remoteFileName);
    }

    /**
     * Download (remote) file
     *
     * @param string $remoteFileName
     * @param string $localFileName
     * @return bool
     */
    public function download($remoteFileName, $localFileName)
    {
        return $this->sftp->get($remoteFileName, $localFileName);
    }

    /**
     * Check if (remote) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists($remoteFileName)
    {
        return $this->sftp->file_exists($remoteFileName);
    }

    /**
     * Get list of files
     *
     * @param string $path
     * @param bool $recursive
     * @return array
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
     * @return mixed
     */
    public function move($currentFileName, $newFileName)
    {
        return $this->sftp->rename($currentFileName, $newFileName);
    }

    /**
     * Upload file
     *
     * @param string $remoteFileName
     * @param string $data
     * @return mixed
     */
    public function upload($remoteFileName, $data)
    {
        return $this->sftp->put($remoteFileName, $data);
    }

}