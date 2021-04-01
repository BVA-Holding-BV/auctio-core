<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\DateTime;
use AuctioCore\Api\Base;

class ReverseLotDetail extends Base {

    /** @var DateTime */
    public DateTime $reverseAuctionDate;
    /** @var int */
    public int $startBidAmount;

}