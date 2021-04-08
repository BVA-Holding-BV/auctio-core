<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class CollectionDay extends Base {

    /** @var int */
    public $id;
    /** @var int */
    public $auctionId;
    /** @var int */
    public $locationId;
    /** @var \AuctioCore\Api\Auctio\Entity\Custom\DateTime */
    public $startDate;
    /** @var \AuctioCore\Api\Auctio\Entity\Custom\DateTime */
    public $endDate;
    /** @var \AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage */
    public $description;

}