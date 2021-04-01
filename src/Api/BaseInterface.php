<?php

namespace AuctioCore\Api;

use stdClass;

interface BaseInterface
{
    /**
     * Loop over all properties and set them in the entity
     * @param stdClass|array $data
     * @return self
     */
    public function populate($data);

    /**
     * Returns a JSON encoded string with current Entity.
     * We have filtered out the readOnly elements
     * @param bool $allowNull
     * @return string|null
     */
    public function encode($allowNull = true): ?string;
}