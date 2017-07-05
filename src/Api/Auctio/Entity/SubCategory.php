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
    /** @var Custom\LocaleMessage */
    public $name;
    /** @var Custom\LocaleMessage */
    public $description;

}