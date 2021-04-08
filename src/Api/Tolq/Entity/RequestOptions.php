<?php

namespace AuctioCore\Api\Tolq\Entity;

use AuctioCore\Api\Base;

class RequestOptions extends Base {

    /** @var string */
    public $context_url;
    /** @var string */
    public $name;
    /** @var boolean */
    public $auto_client_review;
    /** @var string */
    public $callback_url;

}