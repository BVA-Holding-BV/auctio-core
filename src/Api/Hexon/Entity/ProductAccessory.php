<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;

class ProductAccessory extends Base {

    /** @var string */
    public string $stocknumber;
    /** @var integer */
    public int $nr;
    /** @var array */
    public array $name;
    /** @var array */
    public array $description;
    /** @var string */
    public string $code;
    /** @var array */
    public array $factory_designation;
    /** @var string */
    public string $priority;
    /** @var array */
    public array $group;
    /** @var boolean */
    public bool $is_option_pack;
    /** @var integer */
    public int $is_part_of;
    /** @var array */
    public array $hint;
    /** @var string */
    public string $fitment;
    /** @var float */
    public float $price_new;

}