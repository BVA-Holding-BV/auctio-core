<?php

namespace AuctioCore\Api\AdCurve\Entity;

use AuctioCore\Api\Base;

class ProductBatch extends Base {

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
    public $creationDate;
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
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
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
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
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