<?php

namespace AuctioCore\Zf\Service;

class DefaultService
{
    /**
     * @var Error-messages
     */
    private $messages = [];

    /**
     * @var Error-data
     */
    private $errorData = [];

    public function get($id, $output = 'object', $refresh = false)
    {
        return $this->repository->get($id, $output, $refresh);
    }

    public function getList($output = 'object', $filter = NULL, $groupBy = null, $having = null, $orderBy = NULL, $limitRecords = 25, $offset = 0, $paginator = false, $debug = false)
    {
        return $this->repository->getList($output, $filter, $groupBy, $having, $orderBy, $limitRecords, $offset, $paginator, $debug);
    }

    public function create($data, $output = 'object', $overrule = [])
    {
        return $this->repository->create($data, $output, $overrule);
    }

    public function update($id, $data, $output = 'object', $refresh = false)
    {
        return $this->repository->update($id, $data, $output, $refresh);
    }

    public function delete($id, $remove = false, $refresh = false)
    {
        return $this->repository->delete($id, $remove, $refresh);
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
     * @return array|Error
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Reset error-messages and error-data
     */
    public function resetErrors()
    {
        $this->messages = [];
        $this->errorData = [];
    }
}