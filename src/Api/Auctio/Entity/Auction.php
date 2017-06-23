<?php

namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Abs\Base;

class Auction extends Base {

    /** @var int */
    public $id;
    /** @var Custom\LocaleMessage */
    public $name;
    /** @var Custom\LocaleMessage */
    public $description;
    /** @var Custom\LocaleMessage */
    //public $keywords;
    /** @var Custom\LocaleMessage */
    public $auctionClosedText;
    /** @var Custom\LocaleMessage */
    public $bidRestrictionExplanationText;
    /** @var Custom\LocaleMessage */
    public $extraExplanationText;
    /** @var Custom\LocaleMessage */
    public $importantInformation;
    /** @var Custom\LocaleMessage */
    public $auctionAdvertisement;
    /** @var float */
    public $bidLimitDispensationAmount;
    /** @var string */
    public $countryId;
    /** @var Custom\DateTime */
    public $creationDate;
    /** @var Custom\DateTime */
    public $startDate;
    /** @var Custom\DateTime */
    public $endDate;
    /** @var bool */
    public $onlyBusinessCustomersCanBid;
    /** @var bool */
    public $onlyLocalCustomersCanBid;
    /** @var bool */
    public $privateAuction;
    /** @ReadOnly */
    public $privateAuctionText;
    /** @ReadOnly */
    public $privateAuctionApplicationEmail;
    /** @var bool */
    public $showPrivateAuctionOnHomepage;
    /** @var bool */
    public $allowPrivateAuctionApplications;
    /** @var bool */
    public $allowAutomaticPrivateAuctionAccess;
    /** @var bool */
    public $showBanners;
    /** @var Custom\LocaleMessage */
    public $extraTermsText;
    /** @var bool */
    public $extraTermsRequired;
    /** @var bool */
    public $bidLimitRequired;
    /** @var bool */
    public $active;
    /** * @ReadOnly */
    public $termsUrl;
    /** * @ReadOnly */
    public $leafletURL;
    /** * @ReadOnly */
    public $remark;
    /** @var int */
    public $disableBidding;
    /** @var Custom\LocaleMessage */
    public $themeIntroduction;
    /** @var bool */
    public $permissionRequired;
    /** @var int */
    public $bidRangeId;
    /** @var int */
    public $responsibilityUnitId;
    /** @var array */
    public $languages;
    /** @var array */
    public $channelCodes;
    /** @var int */
    public $reverseBidRangeId;
    /**
     * @var string
     * @ReadOnly
     */
    public $createdBy;
}