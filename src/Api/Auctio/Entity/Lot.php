<?php
namespace AuctioCore\Api\Auctio\Entity;

use AuctioCore\Api\Auctio\Entity\Custom\DateTime;
use AuctioCore\Api\Auctio\Entity\Custom\LocaleMessage;
use AuctioCore\Api\Base;

class Lot extends Base {
    const RESERVATION_STATE_FREE = 'FREE';
    const RESERVATION_STATE_RESERVED = 'RESERVED';
    const RESERVATION_STATE_PAID = 'PAID';
    const RESERVATION_STATE_APPROVED = 'APPROVED';

    /** @var int */
    public int $id;
    /** @var int */
    public int $auctionId;
    /** @var int */
    public int $categoryId;
    /** @var string */
    public string $categoryCode;
    /** @var string */
    public string $dossier;
    /** @var string */
    public string $externalDossierNumber;
    /** @var float */
    public float $auctionFeePercentage;
    /** @var string */
    public string $bidMethodType;
    /** @var int */
    public int $number;
    /** @var string */
    public string $numberAddition;
    /**
     * @var string
     * @ReadOnly
     */
    public string $fullNumber;
    /** @var DateTime */
    public DateTime $startDate;
    /** @var DateTime */
    public DateTime $endDate;
    /**
     * @var int
     * @ReadOnly
     */
    public int $endDateDays;
    /**
     * @var int
     * @ReadOnly
     */
    public int $endDateHours;
    /**
     * @var int
     * @ReadOnly
     */
    public int $endDateMinutes;
    /**
     * @var int
     * @ReadOnly
     */
    public int $endDateSeconds;
    /** @var bool */
    public bool $open;
    /** @var LocaleMessage */
    public LocaleMessage $name;
    /** @var LocaleMessage */
    public LocaleMessage $description;
    /** @var string */
    public string $thumbnailUrl;
    /** @var string */
    public string $imageUrl;
    /** @var string */
    public string $lotPageUrl;
    /** @var int */
    public int $startAmount;
    /** @var int */
    public int $minimumAmount;
    /** @var float */
    public float $latestBidAmount;
    /** @var DateTime */
    public DateTime $lastBidTime;
    /** @var int */
    public int $bidCount;
    /** @var int */
    public int $lotTypeId;
    /** @var int */
    public int $locationId;
    /** @var string */
    public string $externalId;
    /** @var string */
    public string $externalURL;
    /** @var string */
    public string $externalSMS;
    /** @var string */
    public string $externalEmailBroker;
    /** @var string */
    public string $externalEmailOwner;
    /** @var bool */
    public bool $approved;
    /** @var bool */
    public bool $assignedExplicitly;
    /** @var float */
    public float $vehicleTaxAmount;
    /** @var LocaleMessage */
    public LocaleMessage $extraDownloadName;
    /** @var string */
    public string $extraDownloadURL;
    /** @var LocaleMessage */
    public LocaleMessage $extraDownload2Name;
    /** @var string */
    public string $extraDownload2URL;
    /** @var int */
    public int $combinationLotId;
    /**
     * @var array
     * @ReadOnly
     */
    public array $lotIds;
    /**
     * @var string
     * @ReadOnly
     */
    public string $reservationState;
    /** @var string */
    public string $currencyCode;
    /** @var float */
    public float $additionalCosts;
    /** @var bool */
    public bool $buyNowEnabled;
    /** @var string */
    public string $buyNowPrice;
    /** @var ReverseLotDetail */
    public ReverseLotDetail $reverseLotDetail;
    /** @var bool */
    public bool $visibleInHotlist;
    /** @var bool */
    public bool $publishOnFacebook;
    /** @var LocaleMessage */
    public LocaleMessage $notificationMessageHeader;
    /** @var bool */
    public bool $publishNotificationMsgDetail;
    /** @var bool */
    public bool $showMessageDetailLink;
}