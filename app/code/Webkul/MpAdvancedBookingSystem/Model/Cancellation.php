<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Model;

use Webkul\MpAdvancedBookingSystem\Api\Data\CancellationInterface;
use Magento\Framework\DataObject\IdentityInterface;

class Cancellation extends \Magento\Framework\Model\AbstractModel implements CancellationInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpAdvancedBookingSystem Cancellation cache tag.
     */
    public const CACHE_TAG = 'cancellation_requests';

    /**
     * @var string
     */
    protected $_cacheTag = 'cancellation_requests';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'cancellation_requests';

    /**
     * Pending Cancellation status code
     */
    public const STATUS_PENDING = 0;

    /**
     * Approved Cancellation status code
     */
    public const STATUS_APPROVED = 1;

    /**
     * Not Approved Cancellation status code
     */
    public const STATUS_NOT_APPROVED = 2;

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Cancellation::class);
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
            return $this->noRouteItems();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Items.
     *
     * @return \Webkul\MpAdvancedBookingSystem\Model\CancelledInfo
     */
    public function noRouteItems()
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
     * @return \Webkul\MpAdvancedBookingSystem\Api\Data\CancellationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
