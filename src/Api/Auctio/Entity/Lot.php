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
    public $dossier;
    /** @var string */
    public $externalDossierNumber;
    /** @var string */
    public $auctionFeePercentage;
    /** @var string */
    public $bidMethodType;
    /** @var string */
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
     * @var string
     * @ReadOnly
     */
    public $endDateDays;
    /**
     * @var string
     * @ReadOnly
     */
    public $endDateHours;
    /**
     * @var string
     * @ReadOnly
     */
    public $endDateMinutes;
    /** @var string */
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
    /** @var string */
    public $startAmount;
    /** @var string */
    public $latestBidAmount;
    /** @var string */
    public $lastBidTime;
    /** @var string */
    public $bidCount;
    /** @var string */
    public $lotTypeId;
    /** @var string */
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
    /** @var string */
    public $vehicleTaxAmount;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraDownloadName;
    /** @var string */
    public $extraDownloadURL;
    /** @var Api\Auctio\Entity\Custom\LocaleMessage */
    public $extraDownload2Name;
    /** @var string */
    public $extraDownload2URL;
    /** @var string */
    public $combinationLotId;
    /**
     * @var string
     * @ReadOnly
     */
    public $lotIds;
    /**
     * @var string
     */
    public $reservationState;
    /** @var Api\Auctio\Entity\ReverseLotDetail */
    public $reverseLotDetail;

}