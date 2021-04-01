<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;
use AuctioCore\Api\Base;

class Location extends Base {

    /** @var int */
    public int $id;
    /** @var LocaleMessage */
    public LocaleMessage $name;
    /** @var string */
    public string $address;
    /** @var string */
    public string $address2;
    /** @var int */
    public int $auctionId;
    /** @var string */
    public string $city;
    /** @var string */
    public string $countryId;
    /** @var string */
    public string $houseNumber;
    /** @var string */
    public string $houseNumberAddition;
    /** @var string */
    public string $postalCode;
    /** @var bool */
    public bool $defaultLocation;
    /** @var string */
    public string $stateName;
    /** @var int */
    public int $stateId;
    /** @var string */
    public string $latitude;
    /** @var string */
    public string $longitude;

}