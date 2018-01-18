<?php

namespace AuctioCore\Protocol;

use \phpseclib\Crypt\RSA;
use \phpseclib\Net\SFTP AS Net_SFTP;

class SFTP
{
    private $client;
    private $messages;
    private $errorData;

    /**
     * Constructor
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $privateKey
     * @param string $privateKeyPassword
     * @return boolean
     */
    public function __construct($hostname, $username, $password, $privateKey = null, $privateKeyPassword = null)
    {
        // Set error-messages
        $this->messages = [];
        $this->errorData = [];

        // Set private-key
        if (!empty($privateKey)) {
            $key = new RSA();
            $key->loadKey($privateKey);
            $key->setPassword($privateKeyPassword);
        }

        // Set client
        $this->client = new Net_SFTP($hostname);
        if (!empty($privateKey)) $res = $this->client->login($username, $key);
        else $res = $this->client->login($username, $password);

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

}