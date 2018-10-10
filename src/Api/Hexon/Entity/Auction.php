<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;

class Auction extends Base {

    /** @var string */
    public $id;
    /** @var array */
    public $name;
    public $type;
    /** @var string */
    public $location;
    /** @var string */
    public $link;
    /** @var Api\Hexon\Entity\Custom\DateTime */
    public $start;
    /** @var Api\Hexon\Entity\Custom\DateTime */
    public $end;
    /** @var integer */
    public $number_of_lots;
    /** @var array */
    public $description;

}