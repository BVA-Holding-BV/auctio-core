<?php

namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\DateTime;
use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;
use AuctioCore\Api\Base;

class Auction extends Base {

    /** @var int */
    public int $id;
    /** @var LocaleMessage */
    public LocaleMessage $name;
    /** @var LocaleMessage */
    public LocaleMessage $description;
    /** @var LocaleMessage */
    public LocaleMessage $auctionClosedText;
    /** @var LocaleMessage */
    public LocaleMessage $bidRestrictionExplanationText;
    /** @var LocaleMessage */
    public LocaleMessage $privateAuctionText;
    /** @var LocaleMessage */
    public LocaleMessage $privateAuctionEmailText;
    /** @var LocaleMessage */
    public LocaleMessage $extraExplanationText;
    /** @var LocaleMessage */
    public LocaleMessage $importantInformation;
    /** @var LocaleMessage */
    public LocaleMessage $auctionAdvertisement;
    /** @var float */
    public float $bidLimitDispensationAmount;
    /** @var string */
    public string $countryId;
    /** @var string */
    public string $currencyCode;
    /** @var DateTime */
    public DateTime $creationDate;
    /** @var DateTime */
    public DateTime $endDate;
    /** @var bool */
    public bool $onlyBusinessCustomersCanBid;
    /** @var bool */
    public bool $onlyLocalCustomersCanBid;
    /** @var bool */
    public bool $privateAuction;
    /** @var bool */
    public bool $showPrivateAuctionOnHomepage;
    /** @var bool */
    public bool $allowPrivateAuctionApplications;
    /** @var bool */
    public bool $allowAutomaticPrivateAuctionAccess;
    /** @var bool */
    public bool $showBanners;
    /** @var LocaleMessage */
    public LocaleMessage $extraTermsText;
    /** @var bool */
    public bool $extraTermsRequired;
    /** @var bool */
    public bool $bidLimitRequired;
    /** @var bool */
    public bool $active;
    /**
     * @var string
     * @ReadOnly
     */
    public string $termsUrl;
    /** @var DateTime */
    public DateTime $startDate;
    /** @var string */
    public string $leafletURL;
    /** var string */
    public string $privateAuctionApplicationEmail;
    /** @var string */
    public string $remark;
    /** @var bool */
    public bool $disableBidding;
    /** @var LocaleMessage */
    public LocaleMessage $themeIntroduction;
    /** @var bool */
    public bool $permissionRequired;
    /** @var int */
    public int $bidRangeId;
    /** @var int */
    public int $channelId;
    /** @var array */
    public array $languages;
    /** @var array */
    public array $channelCodes;
    /** @var int */
    public int $reverseBidRangeId;
    /** @var string */
    public string $createdBy;
    /** @var int */
    public int $reverseAuctionStartOffset;
    /** @var bool */
    public bool $homeDelivery;
    /** @var int */
    public int $businessUnit;
    /** @var string */
    public string $auctionType;
    /** @var bool */
    public bool $sealedBids;
    /** @var LocaleMessage */
    public LocaleMessage $keywords;
    /** @var bool */
    public bool $deliveryCosts;
}