<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class LotMetaData extends Base {

    /** @var int */
    public int $id;
    /** @var array \AuctioCore\Api\Auctio\Entity\MetaData */
    public array $metadata;

}