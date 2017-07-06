<?php

namespace AuctioCore\Api\Adcurve\Entity\Custom;

use AuctioCore\Api\Base;
use AuctioCore\Api\BaseInterface;

class Date extends Base implements BaseInterface {

    public function populate($data) {
        // Get timestamp of data
        if ($data instanceof \DateTime) {
            $timestamp = $data->getTimestamp();
        } else {
            $date = new \DateTime($data);
            $timestamp = $date->getTimestamp();
        }

        // Set new date-time object
        $this->date = new \DateTime();
        $this->date->setTimestamp($timestamp);
        return $this;
    }

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @return string
     */
    public function encode(){
        $data = $this->date->format('Y-m-d');
        return json_encode($data);
    }
}