<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;

class ProductImage extends Base {

    /** @var string */
    public string $stocknumber;
    /** @var integer */
    public int $nr;
    /** @var string */
    public string $image_url;

}