<?php

namespace AuctioCore\Api\Auctio\Entity\Custom;

use AuctioCore\Api\BaseInterface;

class DateTime extends \DateTime implements BaseInterface {

    public function populate($data) {
        if ($data instanceof \DateTime) {
            $timestamp = $data->getTimestamp();
        } else {
            $date = new \DateTime($data);
            $timestamp = $date->getTimestamp();
        }
        $this->setTimestamp($timestamp);
        return $this;
    }

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @return string
     */
    public function encode(){
        return $this->format(self::ISO8601);
    }
}