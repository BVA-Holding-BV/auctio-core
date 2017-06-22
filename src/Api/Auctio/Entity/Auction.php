<?php

namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Abs\Base;

class Auction extends Base {

    /**
     * @var int
     */
    public $id;
    /**
     * @var LocaleMessage
     */
    public $name;
    /**
     * @var LocaleMessage
     */
    public $description;
    /**
     * @var LocaleMessage
     */
    //public $keywords;
    /**
     * @var LocaleMessage
     */
    public $auctionClosedText;
    /**
     * @var LocaleMessage
     */
    public $bidRestrictionExplanationText;
    /**
     * @var LocaleMessage
     */
    public $extraExplanationText;
    /**
     * @var LocaleMessage
     */
    public $importantInformation;
    /**
     * @var LocaleMessage
     */
    public $auctionAdvertisement;
    /**
     * @var float
     */
    public $bidLimitDispensationAmount;
    /**
     * @var int
     */
    public $countryId;
    /**
     * @var Custom\DateTime
     */
    public $creationDate;
    /**
     * @var Custom\DateTime
     */
    public $startDate;
    /**
     * @var Custom\DateTime
     */
    public $endDate;
    /**
     * @var bool
     */
    public $onlyBusinessCustomersCanBid;
    /**
     * @var bool
     */
    public $onlyLocalCustomersCanBid;
    /**
     * @var bool
     */
    public $privateAuction;
    /**
     * @ReadOnly
     */
    public $privateAuctionText;
    /**
     * @ReadOnly
     */
    public $privateAuctionApplicationEmail;
    /**
     * @var bool
     */
    public $showPrivateAuctionOnHomepage;
    /**
     * @var bool
     */
    public $allowPrivateAuctionApplications;
    /**
     * @var bool
     */
    public $allowAutomaticPrivateAuctionAccess;
    /**
     * @var bool
     */
    public $showBanners;
    /**
     * @var LocaleMessage
     */
    public $extraTermsText;
    /**
     * @var bool
     */
    public $extraTermsRequired;
    /**
     * @var bool
     */
    public $bidLimitRequired;
    /**
     * @var bool
     */
    public $active;
    /**
     * @ReadOnly
     */
    public $termsUrl;
    public $leafletURL;
    public $remark;
    /**
     * @var int
     */
    public $disableBidding;
    /**
     * @var LocaleMessage
     */
    public $themeIntroduction;
    /**
     * @var bool
     */
    public $permissionRequired;
    /**
     * @var int
     */
    public $bidRangeId;
    /**
     * @var int
     */
    public $responsibilityUnitId;
    /**
     * @var array
     */
    public $languages;
    /**
     * @var array
     */
    public $channelCodes;
    /**
     * @var int
     */
    public $reverseBidRangeId;
    /**
     * @var string
     * @ReadOnly
     */
    public $createdBy;
}