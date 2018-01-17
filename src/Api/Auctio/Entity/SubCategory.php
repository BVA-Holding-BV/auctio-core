<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class SubCategory extends Base {

    /** @var int */
    public $id;
    /** @var int */
    public $parentLotCategoryId;
    /** @var int */
    public $position;
    /**
     * @var int
     * @ReadOnly
     */
    public $lotCount;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $name;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $description;

}