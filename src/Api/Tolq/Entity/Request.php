<?php

namespace AuctioCore\Api\Tolq\Entity;

use AuctioCore\Api\Base;

class Request extends Base {

    /** @var array */
    public array $request;
    /** @var string */
    public string $source_language_code;
    /** @var string */
    public string $target_language_codes;
    /** @var string */
    public string $quality;
    /** @var RequestOptions */
    public RequestOptions $options;

}