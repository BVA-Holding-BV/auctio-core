<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;
use AuctioCore\Api\Base;

class MetaData extends Base {

    /** @var string */
    public string $key;
    /** @var LocaleMessage */
    public LocaleMessage $value;

}