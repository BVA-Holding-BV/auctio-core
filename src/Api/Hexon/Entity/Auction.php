<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;
use AuctioCore\Api\Hexon\Entity\Custom\DateTime;

class Auction extends Base {

    /** @var string */
    public string $id;
    /** @var array */
    public array $name;
    public $type;
    /** @var string */
    public string $location;
    /** @var string */
    public string $link;
    /** @var DateTime */
    public DateTime $start;
    /** @var DateTime */
    public DateTime $end;
    /** @var integer */
    public int $number_of_lots;
    /** @var array */
    public array $description;

}