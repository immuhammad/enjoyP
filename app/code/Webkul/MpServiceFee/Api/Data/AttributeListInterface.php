<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Api\Data;

interface AttributeListInterface
{

    public const ENTITY_ID = 'entity_id';

    public const SERVICE_STATUS = 'service_status';

    public const SERVICE_CODE = 'service_code';

    public const SERVICE_TITLE = 'service_title';

    public const SERVICE_VALUE = 'service_value';

    public const SERVICE_TYPE = 'service_type';

    public const SELLER_ID = 'seller_id';

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setId($entityId);

    /**
     * Get EntityId
     *
     * @return int
     */
    public function getId();

    /**
     * Set ServiceStatus
     *
     * @param int $serviceStatus
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setServiceStatus($serviceStatus);

    /**
     * Get ServiceStatus
     *
     * @return int
     */
    public function getServiceStatus();

    /**
     * Set ServiceCode
     *
     * @param string $serviceCode
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setServiceCode($serviceCode);

    /**
     * Get ServiceCode
     *
     * @return string
     */
    public function getServiceCode();

    /**
     * Set ServiceTitle
     *
     * @param string $serviceTitle
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setServiceTitle($serviceTitle);

    /**
     * Get ServiceTitle
     *
     * @return string
     */
    public function getServiceTitle();

    /**
     * Set ServiceValue
     *
     * @param int $serviceValue
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setServiceValue($serviceValue);

    /**
     * Get ServiceValue
     *
     * @return int
     */
    public function getServiceValue();

    /**
     * Set ServiceType
     *
     * @param string $serviceType
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setServiceType($serviceType);

    /**
     * Get ServiceType
     *
     * @return string
     */
    public function getServiceType();

    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpServiceFee\Api\Data\AttributeListInterface
     */
    public function setSellerId($sellerId);
    
    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId();
}
