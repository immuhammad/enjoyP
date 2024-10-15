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
namespace Webkul\MpServiceFee\Model;

use Magento\Framework\DataObject\IdentityInterface as Identity;
use Magento\Framework\Model\AbstractModel;
use Webkul\MpServiceFee\Api\Data\AttributeListInterface;

class AttributesList extends AbstractModel implements Identity, AttributeListInterface
{
    
    /**
     * ProductDetail Gallery cache tag
     */
    public const CACHE_TAG = 'service_fees';

    /**
     * @var string
     */
    protected $_cacheTag = 'service_fees';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'service_fees';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpServiceFee\Model\ResourceModel\AttributesList::class);
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteItem();
        }
        return parent::load($id, $field);
    }

    /**
     * Load No-Route Item.
     *
     * @return \Webkul\MpServiceFee\Model\AttributesList
     */
    public function noRouteItem()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * Set EntityId
     *
     * @param int $entityId
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * Get EntityId
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ServiceStatus
     *
     * @param int $serviceStatus
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setServiceStatus($serviceStatus)
    {
        return $this->setData(self::SERVICE_STATUS, $serviceStatus);
    }

    /**
     * Get ServiceStatus
     *
     * @return int
     */
    public function getServiceStatus()
    {
        return parent::getData(self::SERVICE_STATUS);
    }

    /**
     * Set ServiceCode
     *
     * @param string $serviceCode
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setServiceCode($serviceCode)
    {
        return $this->setData(self::SERVICE_CODE, $serviceCode);
    }

    /**
     * Get ServiceCode
     *
     * @return string
     */
    public function getServiceCode()
    {
        return parent::getData(self::SERVICE_CODE);
    }

    /**
     * Set ServiceTitle
     *
     * @param string $serviceTitle
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setServiceTitle($serviceTitle)
    {
        return $this->setData(self::SERVICE_TITLE, $serviceTitle);
    }

    /**
     * Get ServiceTitle
     *
     * @return string
     */
    public function getServiceTitle()
    {
        return parent::getData(self::SERVICE_TITLE);
    }

    /**
     * Set ServiceValue
     *
     * @param int $serviceValue
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setServiceValue($serviceValue)
    {
        return $this->setData(self::SERVICE_VALUE, $serviceValue);
    }

    /**
     * Get ServiceValue
     *
     * @return int
     */
    public function getServiceValue()
    {
        return parent::getData(self::SERVICE_VALUE);
    }

    /**
     * Set ServiceType
     *
     * @param string $serviceType
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setServiceType($serviceType)
    {
        return $this->setData(self::SERVICE_TYPE, $serviceType);
    }

    /**
     * Get ServiceType
     *
     * @return string
     */
    public function getServiceType()
    {
        return parent::getData(self::SERVICE_TYPE);
    }

    /**
     * Set SellerId
     *
     * @param int $sellerId
     * @return Webkul\MpServiceFee\Model\AttributeListInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get SellerId
     *
     * @return int
     */
    public function getSellerId()
    {
        return parent::getData(self::SELLER_ID);
    }
}
