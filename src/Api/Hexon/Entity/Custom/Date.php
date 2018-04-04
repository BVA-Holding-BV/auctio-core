<?php

namespace AuctioCore\Api\Hexon\Entity\Custom;

use AuctioCore\Api\Base;
use AuctioCore\Api\BaseInterface;

class Date extends Base implements BaseInterface {

    public function populate($data) {
        if ($data instanceof \DateTime) {
            $timestamp = $data->getTimestamp();
        } elseif (is_array($data)) {
            $date = new \DateTime($data['date']);
            $timestamp = $date->getTimestamp();
        } else {
            $date = new \DateTime($data);
            $timestamp = $date->getTimestamp();
        }

        // Set new date-time object
        $this->date = new \DateTime();
        $this->date->setTimestamp($timestamp);
        return $this->date;
    }

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @return string
     */
    public function encode(){
        // Set timezone to UTC
        $this->setTimezone(new \DateTimeZone('UTC'));

        // Return
        return $this->format("Y-m-d");
    }
}