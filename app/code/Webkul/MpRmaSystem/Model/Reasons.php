<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model;

use Webkul\MpRmaSystem\Api\Data\ReasonsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * MpRmaSystem Reasons Model.
 *
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Reasons _getResource()
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Reasons getResource()
 */
class Reasons extends AbstractModel implements ReasonsInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpRmaSystem Reasons cache tag.
     */
    public const CACHE_TAG = 'mprmasystem_reasons';

    /**
     * @var string
     */
    protected $_cacheTag = 'mprmasystem_reasons';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mprmasystem_reasons';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRmaSystem\Model\ResourceModel\Reasons::class);
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
            return $this->noRouteReasons();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Reasons.
     *
     * @return \Webkul\MpRmaSystem\Model\Reasons
     */
    public function noRouteReasons()
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
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get REASON.
     *
     * @return string|null
     */
    public function getReason()
    {
        return parent::getData(self::REASON);
    }

    /**
     * Set REASON.
     *
     * @param string $reason
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setReason($reason)
    {
        return $this->setData(self::REASON, $reason);
    }

    /**
     * Get STATUS.
     *
     * @return int|null
     */
    public function getStatus()
    {
        return parent::getData(self::STATUS);
    }

    /**
     * Set STATUS.
     *
     * @param int $status
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
}
