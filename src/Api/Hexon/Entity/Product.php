<?php

namespace AuctioCore\Api\Hexon\Entity;

use AuctioCore\Api\Base;

class Product extends Base {

    /** @var string */
    public $stocknumber;
    /** @var boolean */
    public $identificationStocknumber_public;
    public $identificationReferencenumber;
    /** @var string */
    public $identificationLicense_plate;
    public $identificationVin;
    public $identificationTypenumber;
    public $identificationSerialnumber;
    /** @var string */
    public $identificationDirect_link;
    /** @var string */
    public $generalCategory;
    /** @var string */
    public $generalBodystyle;
    /** @var string */
    public $generalMakeName;
    /** @ReadOnly */
    public $generalMakeId;
    /** @var string */
    public $generalModelName;
    public $generalModelCode;
    public $generalModelStart;
    public $generalModelEnd;
    /** @var string */
    public $generalTypeName;
    public $generalApplicationField;
    public $generalApplicationMaterial;
    public $generalApplicationScale;
    /** @var integer */
    public $bodyDoor_count;
    public $bodyDimensionsLength;
    public $bodyDimensionsWidth;
    public $bodyDimensionsHeight;
    public $bodyMax_roof_load;
    /** @var string */
    public $bodyColourPrimary = "other";
    public $bodyColourName;
    public $bodyColourTint;
    public $bodyColourPaint_type;
    public $bodyCabinLength;
    public $bodyCabinSleeper_cab;
    public $bodyCabinModel;
    /** @var integer */
    public $interiorSeat_count;
    public $interiorBed_count;
    public $interiorColour;
    public $interiorUpholstery;
    public $powertrainEngineMake;
    public $powertrainEngineModel;
    public $powertrainEngineCylinder_count;
    public $powertrainEngineDisplacement;
    /** @var integer */
    public $powertrainEnginePowerValue;
    /** @var string */
    public $powertrainEnginePowerUnit;
    /** @var string */
    public $powertrainEngineEnergyType;
    public $powertrainEngineEnergyFuelConsumptionCombined;
    public $powertrainEngineEnergyFuelConsumptionUrban;
    public $powertrainEngineEnergyFuelConsumptionExtra_urban;
    public $powertrainEngineEnergyFuelTank_capacity;
    public $powertrainEngineEnergyFuelOctane_rating;
    public $powertrainEngineEnergyFuelE10_suitable;
    public $powertrainEngineEnergyElectricityConsumptionCombined;
    public $powertrainEngineEnergyElectricityConsumptionUrban;
    public $powertrainEngineEnergyElectricityConsumptionExtra_urban;
    public $powertrainEngineEnergyRange;
    public $powertrainTransmissionGear_count;
    /** @var string */
    public $powertrainTransmissionType;
    public $powertrainTransmissionMake;
    public $powertrainTransmissionModel;
    public $powertrainEmissionsClass;
    public $powertrainEmissionsCo2;
    public $powertrainEmissionsParticulates;
    public $powertrainEmissionsEnergy_label;
    public $powertrainAxlesCount;
    public $powertrainAxlesPowered_axles_count;
    public $powertrainAxlesWheelbase;
    public $powertrainWheelsCount;
    public $powertrainWheelsDiameter;
    public $powertrainTyresHeight;
    public $powertrainTyresWidth;
    public $powertrainTyresSeason;
    public $powertrainAcceleration;
    public $powertrainTopspeed;
    public $chassisMake;
    public $chassisModel;
    public $chassisType;
    public $chassisSteering;
    public $superstructureMake;
    public $superstructureModel;
    public $superstructureDimensionsExtended;
    public $superstructureDimensionsHeightened;
    public $superstructureDimensionsLength;
    public $superstructureDimensionsWidth;
    public $superstructureDimensionsHeight;
    public $superstructurePump;
    public $superstructureHigh_pressure_pump;
    public $superstructureLoading_platform_height;
    public $superstructureSliding_roof;
    public $superstructureExtendable;
    public $superstructureCar_capacity;
    public $superstructureCompartment_count;
    public $superstructureHoses;
    public $superstructureCounter;
    public $superstructureWater_system;
    public $superstructureClean_water_tankPresent;
    public $superstructureClean_water_tankCapacity;
    public $superstructureTipperSide_count;
    public $superstructureTipperTips_left;
    public $superstructureTipperTips_right;
    public $superstructureTipperTips_back;
    public $superstructureWall_thickness;
    public $superstructureTemperature_controlTemperatureMin;
    public $superstructureTemperature_controlTemperatureMax;
    public $superstructureTemperature_controlEngine_type;
    public $superstructureTemperature_controlRunning_hoursDiesel;
    public $superstructureTemperature_controlRunning_hoursElectric;
    public $superstructureTailgatePresent;
    public $superstructureTailgateMake;
    public $superstructureTailgateModel;
    public $superstructureTailgateType;
    public $superstructureTailgateCapacity;
    public $superstructureCranePresent;
    public $superstructureCraneMake;
    public $superstructureCraneModel;
    public $superstructureCraneYear;
    public $superstructureCranePosition;
    public $weightsMass_empty;
    public $weightsPayload;
    /** @var integer */
    public $weightsGvw;
    public $weightsTrailer_loadBraked;
    public $weightsTrailer_loadUnbraked;
    /** @var integer */
    public $conditionKey_count;
    public $conditionHand_transmitter_count;
    /** @var boolean */
    public $conditionUsed;
    /** @var integer */
    public $conditionOdometerReading;
    /** @var string */
    public $conditionOdometerUnit;
    /** @var integer */
    public $conditionOperating_hours;
    public $conditionStateGeneral;
    public $conditionStateTechnical;
    public $conditionStateOptical;
    public $conditionDamageState;
    /** @var array */
    public $conditionDamageRemarks;
    public $conditionDamageRepair_costs;
    public $classificationDemonstrator;
    public $classificationRental;
    public $classificationConsignment;
    public $classificationOldtimer;
    public $classificationClassic;
    public $classificationCe_marking;
    public $classificationEuro_ncap;
    /** @var string */
    public $sales_conditionsPricingCurrency = "EUR";
    public $sales_conditionsPricingType;
    public $sales_conditionsPricingNew;
    /** @var float */
    public $sales_conditionsPricingConsumerValue;
    /** @var boolean */
    public $sales_conditionsPricingConsumerIncl_vat;
    public $sales_conditionsPricingDiscountedValue;
    public $sales_conditionsPricingDiscountedIncl_vat;
    public $sales_conditionsPricingAs_isValue;
    public $sales_conditionsPricingAs_isIncl_vat;
    public $sales_conditionsPricingTradeValue;
    public $sales_conditionsPricingTradeIncl_vat;
    public $sales_conditionsPricingExportValue;
    public $sales_conditionsPricingExportIncl_vat;
    public $sales_conditionsPricingFiscal_value;
    public $sales_conditionsBiddingAllowed;
    public $sales_conditionsBiddingMinimumValue;
    public $sales_conditionsBiddingMinimumUnit;
    public $sales_conditionsBiddingReserve;
    /** @var boolean */
    public $sales_conditionsMargin_scheme;
    public $sales_conditionsDelivery_costs;
    public $sales_conditionsExpected;
    public $sales_conditionsReserved;
    public $sales_conditionsWarrantyManufacturerMonths;
    public $sales_conditionsWarrantyManufacturerEnd_date;
    public $sales_conditionsWarrantyManufacturerMax_distance;
    public $sales_conditionsWarrantyBrandCode;
    public $sales_conditionsWarrantyOrganizationBovag_warrantyOffered;
    public $sales_conditionsWarrantyOrganizationBovag_warrantyMonths;
    public $sales_conditionsWarrantyOrganizationVetos_warranty;
    public $sales_conditionsWarrantyOrganizationPca_top_occasion;
    public $sales_conditionsWarrantyOrganizationBcs_warranty;
    public $sales_conditionsWarrantyOrganizationVakgarant_premium_occasion;
    public $sales_conditionsWarrantyOrganizationAutotrust_warranty;
    public $sales_conditionsWarrantyOrganizationVwe_occasion_garant_plan;
    public $sales_conditionsWarrantyOrganizationCar_warranty;
    public $sales_conditionsWarrantyDealerCode;
    public $sales_conditionsWarrantyDealerMonths;
    public $sales_conditionsWarrantyDealerMax_distance;
    public $lease_contractType;
    public $lease_contractName;
    public $lease_contractStart_date;
    public $lease_contractEnd_date;
    public $lease_contractMonthly_installment;
    public $lease_contractOriginal_monthly_installment;
    public $lease_contractAnnual_distance;
    public $lease_contractRemaining_distance;
    public $lease_contractOver_distance_penalty;
    public $lease_contractUnder_distance_refund;
    public $lease_contractDown_payment;
    public $lease_contractSecurity_deposit;
    public $lease_contractInterest_rate;
    public $lease_contractRemaining_balance;
    public $lease_contractPurchase_possible;
    public $lease_contractRemarks;
    /** @var string */
    public $historyConstruction_date;
    public $historyFirst_registrationCountry;
    /** @var \AuctioCore\Api\Hexon\Entity\Custom\Date */
    public $historyFirst_registrationDate;
    /** @var string */
    public $historyCurrent_registrationCountry = "nl";
    /** @var \AuctioCore\Api\Hexon\Entity\Custom\Date */
    public $historyCurrent_registrationFirst_admission;
    public $historyDate_last_ownership_change;
    public $historyMaintenance_booklet;
    public $historyPrevious_owner_count;
    /** @var array */
    public $descriptionRemarks;
    /** @var array */
    public $descriptionRemarks_trade;
    /** @var integer */
    public $descriptionDefault_remark = 1;
    public $descriptionTitle;
    public $descriptionHighlights;
    public $descriptionSearch_terms;
    public $category_specificCarsBoot_capacitySeats_up;
    public $category_specificCarsBoot_capacitySeats_down;
    public $category_specificBicyclesUser;
    public $category_specificTrailersBraked;
    public $category_specificMachineryForksLengthValue;
    public $category_specificMachineryForksLengthUnit;
    public $category_specificMachineryForksWidthValue;
    public $category_specificMachineryForksWidthUnit;
    public $category_specificMachineryBatteryCapacityValue;
    public $category_specificMachineryBatteryCapacityUnit;
    public $category_specificMachineryBatteryVoltage;
    public $category_specificMachineryBatteryMake;
    public $category_specificMachineryBatteryYear;
    public $category_specificMachineryBatteryMonth;
    public $category_specificMachineryMastLength;
    public $category_specificMachineryMastType;
    public $category_specificMachineryMastStage_count;
    public $category_specificMachineryPump_connection_size;
    public $category_specificMachineryGenerator_power;
    public $category_specificMachineryCompressor_type;
    public $category_specificMachineryThroughput_capacityValue;
    public $category_specificMachineryThroughput_capacityUnit;
    public $category_specificMachineryMax_reach;
    public $category_specificMachineryVoltage_rating;
    public $category_specificMachineryJib_lengthValue;
    public $category_specificMachineryJib_lengthUnit;
    public $category_specificMachineryBucket_capacityValue;
    public $category_specificMachineryBucket_capacityUnit;
    public $category_specificMachineryWorking_width;
    public $category_specificMachineryWorking_height;
    public $category_specificMachineryLifting_height;
    public $category_specificMachineryFreelift;
    public $category_specificMachineryLifting_capacity;
    public $category_specificMachineryTransportationImmobile;
    public $category_specificMachineryTransportationMethod;
    /** @var array */
    public $category_specificMachineryTransportationPropulsion;
    public $category_specificMachineryTransportationSurface;
    /** @var string */
    public $category_specificMachineryTransportationDriver_position;
    public $category_specificMachineryAutonomous;
    public $category_specificMachineryCrusher_type;
    public $category_specificMachineryRollerVibrating;
    public $category_specificMachineryRollerSurface;
    public $category_specificMachineryQuick_coupler;
    public $category_specificMachineryPantograph;
    public $category_specificAttachmentsFits_to;
    public $category_specificAttachmentsHoist_connection;
    public $category_specificPartsIntended_positionLongitude;
    public $category_specificPartsIntended_positionLatitude;
    public $category_specificBoatsDepth;
    public $category_specificBoatsHull_material;
    public $category_specificBoatsBerth;
    /** @var \AuctioCore\Api\Hexon\Entity\Custom\Date */
    public $region_specificNlApk_date;
    public $region_specificNlApk_delivery;
    /** @var integer */
    public $region_specificNlBpm_amount;
    public $region_specificNlPrice_consumer_incl_bpm;
    public $region_specificNlPrice_discounted_incl_bpm;
    public $region_specificNlPrice_as_is_incl_bpm;
    public $region_specificNlPrice_trade_incl_bpm;
    public $region_specificNlIncome_tax_addition_percentage;
    /** @var string */
    public $auctionId;
    /** @var string */
    public $auctionClient_id;
    /** @var string */
    public $auctionLink;
    public $extra_fields;

}