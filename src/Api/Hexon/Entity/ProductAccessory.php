<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;

class ProductAccessory extends Base {

    /** @var string */
    public $stocknumber;
    /** @var integer */
    public $nr;
    /** @var array */
    public $name;
    /** @var array */
    public $description;
    /** @var string */
    public $code;
    /** @var array */
    public $factory_designation;
    /** @var string */
    public $priority;
    /** @var array */
    public $group;
    /** @var boolean */
    public $is_option_pack;
    /** @var integer */
    public $is_part_of;
    /** @var array */
    public $hint;
    /** @var string */
    public $fitment;
    /** @var float */
    public $price_new;

}