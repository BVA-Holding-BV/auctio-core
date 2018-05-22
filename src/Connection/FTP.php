<?php

namespace AuctioCore\Connection;

class FTP
{
    private $ftp;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     */
    public function __construct($hostname, $username, $password)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Set ftp
        $this->ftp = ftp_connect($hostname);

        // Check if login available
        if (!empty($username)) {
            // Login
            $login = ftp_login($this->ftp, $username, $password);
            if (!$login) {
                $this->setMessages('Login failed');
                return false;
            } else {
                ftp_pasv($this->ftp, true);
            }
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
     * Create a directory
     *
     * @param string $dir
     * @return bool
     */
    public function createDirectory($dir)
    {
        return ftp_mkdir($this->ftp, $dir);
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
        return ftp_get($this->ftp, $localFileName, $remoteFileName, FTP_BINARY);
    }

    /**
     * Check if (remote) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists($remoteFileName)
    {
        $res = ftp_size($this->ftp, $remoteFileName);
        return ($res >= 0) ? true : false;
    }

    /**
     * Get list of files
     *
     * @param string $path
     * @return array
     */
    public function getFiles($path = null)
    {
        return ftp_nlist($this->ftp, $path);
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
        return ftp_rename($this->ftp, $currentFileName, $newFileName);
    }

    /**
     * Close ftp-connection
     *
     * @return boolean
     */
    public function close()
    {
        return ftp_close($this->ftp);
    }
}