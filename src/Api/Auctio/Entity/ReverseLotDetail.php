<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class ReverseLotDetail extends Base {

    /** @var \AuctioCore\Api\Auctio\Entity\Custom\DateTime */
    public $reverseAuctionDate;
    /** @var int */
    public $startBidAmount;

}