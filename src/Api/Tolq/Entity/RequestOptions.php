<?php

namespace AuctioCore\Api\Tolq\Entity;

use AuctioCore\Api\Base;

class RequestOptions extends Base {

    /** @var string */
    public string $context_url;
    /** @var string */
    public string $name;
    /** @var boolean */
    public bool $auto_client_review;
    /** @var string */
    public string $callback_url;

}