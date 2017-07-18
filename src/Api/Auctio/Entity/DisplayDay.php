<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class DisplayDay extends Base {

    /** @var int */
    public $id;
    /** @var int */
    public $auctionId;
    /** @var int */
    public $locationId;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $startDate;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $endDate;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $description;

}