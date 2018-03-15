<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class MetaData extends Base {

    /** @var string */
    public $key;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $value;

}