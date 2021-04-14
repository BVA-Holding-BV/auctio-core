<?php

namespace AuctioCore\Connection;

class FTP
{
    private $ftp;
    private array $messages;
    private array $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     */
    public function __construct(string $hostname, string $username, string $password)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Set ftp
        $this->ftp = ftp_connect($hostname);
        if ($this->ftp === false) {
            $this->setMessages('Connection failed');
            return false;
        }

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
     * @return false|string
     */
    public function createDirectory(string $dir)
    {
        if ($this->ftp === false) return false;
        return ftp_mkdir($this->ftp, $dir);
    }

    /**
     * Delete a directory
     *
     * @param string $dir
     * @return bool
     */
    public function deleteDirectory(string $dir): bool
    {
        if ($this->ftp === false) return false;
        return ftp_rmdir($this->ftp, $dir);
    }

    /**
     * Delete a file
     *
     * @param string $file
     * @return bool
     */
    public function deleteFile(string $file): bool
    {
        if ($this->ftp === false) return false;
        return ftp_delete($this->ftp, $file);
    }

    /**
     * Download (remote) file
     *
     * @param string $remoteFileName
     * @param string $localFileName
     * @return bool
     */
    public function download(string $remoteFileName, string $localFileName): bool
    {
        if ($this->ftp === false) return false;
        return ftp_get($this->ftp, $localFileName, $remoteFileName, FTP_BINARY);
    }

    /**
     * Check if (remote) file exists
     *
     * @param string $remoteFileName
     * @return bool
     */
    public function exists(string $remoteFileName): bool
    {
        if ($this->ftp === false) return false;

        // Check if directory exists
        $res = ftp_nlist($this->ftp, $remoteFileName);
        if (is_array($res)) return true;

        // Check if file exists
        $res = ftp_size($this->ftp, $remoteFileName);
        return $res >= 0;
    }

    /**
     * Get list of files
     *
     * @param string $path
     * @return array|false
     */
    public function getFiles($path = null)
    {
        if ($this->ftp === false) return false;
        return ftp_nlist($this->ftp, $path);
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
        if ($this->ftp === false) return false;
        return ftp_rename($this->ftp, $currentFileName, $newFileName);
    }

    /**
     * Close ftp-connection
     *
     * @return boolean
     */
    public function close(): bool
    {
        if ($this->ftp === false) return false;
        return ftp_close($this->ftp);
    }
}