<?php

namespace AuctioCore\Laminas\Service;

use AuctioCore\Laminas\Repository\AbstractRepository;

class DefaultService
{
    /**
     * @var array $messages Error-messages
     */
    private array $messages = [];

    /**
     * @var array $errorData Error-data
     */
    private array $errorData = [];
    protected AbstractRepository $repository;

    public function get($id, $output = 'object', $refresh = false)
    {
        $res = $this->repository->get($id, $output, $refresh);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function getByParameters($parameters, $output = 'object', $multiple = true)
    {
        $res = $this->repository->getByParameters($parameters, $output, $multiple);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function getList($output = 'object', $filter = NULL, $groupBy = null, $having = null, $orderBy = NULL, $limitRecords = 25, $offset = 0, $paginator = false, $debug = false)
    {
        $res = $this->repository->getList($output, $filter, $groupBy, $having, $orderBy, $limitRecords, $offset, $paginator, $debug);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function create($data, $output = 'object', $overrule = [])
    {
        $res = $this->repository->create($data, $output, $overrule);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function update($id, $data, $output = 'object', $refresh = false)
    {
        $res = $this->repository->update($id, $data, $output, $refresh);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function updateBulk($data, $output = 'object', $refresh = false)
    {
        $res = $this->repository->updateBulk($data, $output, $refresh);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
    }

    public function delete($id, $remove = false, $refresh = false)
    {
        $res = $this->repository->delete($id, $remove, $refresh);
        if ($res === false) {
            $this->setMessages($this->repository->getMessages());
            $this->setErrorData($this->repository->getErrorData());
        }
        return $res;
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
     * Reset error-messages and error-data
     */
    public function resetErrors()
    {
        $this->messages = [];
        $this->errorData = [];
    }
}