<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class ReverseLotDetail extends Base {

    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $reverseAuctionDate;
    /** @var int */
    public $startBidAmount;

}