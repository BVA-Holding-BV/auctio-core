<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\DateTime;
use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;
use AuctioCore\Api\Base;

class DisplayDay extends Base {

    /** @var int */
    public int $id;
    /** @var int */
    public int $auctionId;
    /** @var int */
    public int $locationId;
    /** @var DateTime */
    public DateTime $startDate;
    /** @var DateTime */
    public DateTime $endDate;
    /** @var LocaleMessage */
    public LocaleMessage $description;

}