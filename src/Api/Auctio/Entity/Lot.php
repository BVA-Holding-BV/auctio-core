<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Base;

class Lot extends Base {
    const RESERVATION_STATE_FREE = 'FREE';
    const RESERVATION_STATE_RESERVED = 'RESERVED';
    const RESERVATION_STATE_PAID = 'PAID';
    const RESERVATION_STATE_APPROVED = 'APPROVED';

    /** @var int */
    public $id;
    /** @var int */
    public $auctionId;
    /** @var int */
    public $categoryId;
    /** @var string */
    public $categoryCode;
    /** @var string */
    public $dossier;
    /** @var string */
    public $externalDossierNumber;
    /** @var float */
    public $auctionFeePercentage;
    /** @var string */
    public $bidMethodType;
    /** @var int */
    public $number;
    /** @var string */
    public $numberAddition;
    /**
     * @var string
     * @ReadOnly
     */
    public $fullNumber;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $startDate;
    /** @var Api\Auctio\Entity\Custom\DateTime */
    public $endDate;
    /**
     * @var int
     * @ReadOnly
     */
    public $endDateDays;
    /**
     * @var int
     * @ReadOnly
     */
    public $endDateHours;
    /**
     * @var int
     * @ReadOnly
     */
    public $endDateMinutes;
    /**
     * @var int
     * @ReadOnly
     */
    public $endDateSeconds;
    /** @var bool */
    public $open;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $name;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $description;
    /** @var string */
    public $thumbnailUrl;
    /** @var string */
    public $imageUrl;
    /** @var string */
    public $lotPageUrl;
    /** @var int */
    public $startAmount;
    /** @var int */
    public $minimumAmount;
    /** @var float */
    public $latestBidAmount;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $lastBidTime;
    /** @var int */
    public $bidCount;
    /** @var int */
    public $lotTypeId;
    /** @var int */
    public $locationId;
    /** @var string */
    public $externalId;
    /** @var string */
    public $externalURL;
    /** @var string */
    public $externalSMS;
    /** @var string */
    public $externalEmailBroker;
    /** @var string */
    public $externalEmailOwner;
    /** @var bool */
    public $approved;
    /** @var bool */
    public $assignedExplicitly;
    /** @var float */
    public $vehicleTaxAmount;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraDownloadName;
    /** @var string */
    public $extraDownloadURL;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraDownload2Name;
    /** @var string */
    public $extraDownload2URL;
    /** @var int */
    public $combinationLotId;
    /**
     * @var array
     * @ReadOnly
     */
    public $lotIds;
    /**
     * @var string
     * @ReadOnly
     */
    public $reservationState;
    /** @var string */
    public $currencyCode;
    /** @var float */
    public $additionalCosts;
    /** @var bool */
    public $buyNowEnabled;
    /** @var string */
    public $buyNowPrice;
    /** @var Api\Auctio\Entity\ReverseLotDetail */
    public $reverseLotDetail;

}