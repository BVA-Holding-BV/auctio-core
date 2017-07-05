<?php
namespace AuctioCore\Api\AdCurve\Entity;

use AuctioCore\Api\Base;

class Product extends Base {

    /** @var int */
    public $id;
    /** @var Custom\LocaleMessage */
    public $name;
    /** @var string */
    public $address;
    /** @var string */
    public $address2;
    /** @var int */
    public $auctionId;
    /** @var string */
    public $city;
    /** @var string */
    public $countryId;
    /** @var string */
    public $houseNumber;
    /** @var string */
    public $houseNumberAddition;
    /** @var string */
    public $postalCode;
    /** @var bool */
    public $defaultLocation;
    /** @var string */
    public $stateName;
    /** @var string */
    public $stateId;
    /** @var string */
    public $latitude;
    /** @var string */
    public $longitude;

}