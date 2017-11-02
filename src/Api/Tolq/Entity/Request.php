<?php

namespace AuctioCore\Api\Tolq\Entity;

use AuctioCore\Api\Base;

class Request extends Base {

    /** @var array */
    public $request;
    /** @var string */
    public $source_language_code;
    /** @var string */
    public $target_language_codes;
    /** @var string */
    public $quality;
    /** #var Api\Tolq\Entity\RequestOptions */
    public $options;

}