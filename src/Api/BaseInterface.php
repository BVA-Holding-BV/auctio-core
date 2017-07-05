<?php

namespace AuctioCore\Api;

interface BaseInterface
{
    /**
     * Loop over all properties and set them in the entity
     * @param mixed $data
     * @return self
     */
    public function populate($data);

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @return string
     */
    public function encode();
}