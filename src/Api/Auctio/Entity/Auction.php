<?php

namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class Auction extends Base {

    /** @var int */
    public $id;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $name;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $description;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    //public $keywords;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $auctionClosedText;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $privateAuctionText;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $privateAuctionEmailText;
    /** var string */
    public $privateAuctionApplicationEmail;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $bidRestrictionExplanationText;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraExplanationText;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $importantInformation;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $auctionAdvertisement;
    /** @var float */
    public $bidLimitDispensationAmount;
    /** @var string */
    public $countryId;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $startDate;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $endDate;
    /** @var bool */
    public $onlyBusinessCustomersCanBid;
    /** @var bool */
    public $onlyLocalCustomersCanBid;
    /** @var bool */
    public $privateAuction;
    /** @var bool */
    public $showPrivateAuctionOnHomepage;
    /** @var bool */
    public $allowPrivateAuctionApplications;
    /** @var bool */
    public $allowAutomaticPrivateAuctionAccess;
    /** @var bool */
    public $showBanners;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraTermsText;
    /** @var bool */
    public $extraTermsRequired;
    /** @var bool */
    public $bidLimitRequired;
    /** @var bool */
    public $active;
    /** @ReadOnly */
    public $termsUrl;
    /** @var string */
    public $leafletURL;
    /** @var string */
    public $remark;
    /** @var int */
    public $disableBidding;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $themeIntroduction;
    /** @var bool */
    public $permissionRequired;
    /** @var int */
    public $bidRangeId;
    /** @var int */
    public $channelId;
    /** @var array */
    public $languages;
    /** @var array */
    public $channelCodes;
    /** @var int */
    public $reverseBidRangeId;
    /** @var int */
    public $reverseAuctionStartOffset;
    /** @var string */
    public $currencyCode;
    /** @var bool */
    public $homeDelivery;
    /** @var string */
    public $createdBy;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $creationDate;

}