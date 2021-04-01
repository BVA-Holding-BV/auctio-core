<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;
use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;

class Category extends Base {

    /** @var LocaleMessage */
    public LocaleMessage $name;

}