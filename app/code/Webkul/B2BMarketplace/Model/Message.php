<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Model;

use Webkul\B2BMarketplace\Api\Data\MessageInterface;
use Magento\Framework\DataObject\IdentityInterface as Identity;
use Magento\Framework\Model\AbstractModel;

class Message extends AbstractModel implements MessageInterface, Identity
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Message cache tag.
     */
    const CACHE_TAG = 'b2b_message';

    /**
     * @var string
     */
    protected $_cacheTag = 'b2b_message';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'b2b_message';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\B2BMarketplace\Model\ResourceModel\Message');
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
     * Load No-Route Message.
     *
     * @return \Webkul\B2BMarketplace\Model\Message
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
     * @return \Webkul\B2BMarketplace\Api\Data\MessageInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Saving product type related data and init index
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave()
    {
        $this->updateCustomerListData();
        $result = parent::afterSave();
        return $result;
    }

    public function updateCustomerListData()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->create('Webkul\B2BMarketplace\Helper\Data');
        $model = $objectManager->create('Webkul\B2BMarketplace\Model\CustomerList');
        $collection = $model->getCollection();
        $receiverId = $this->getReceiverId();
        if ((int) $this->getListId() == 0) {
            if ($this->getType() == 1) {
                $senderId = $helper->getSupplierId();
                $collection->addFieldToFilter("supplier_id", $senderId);
                $collection->addFieldToFilter("customer_id", $receiverId);
                if ($collection->getSize()) {
                    foreach ($collection as $item) {
                        $listId = $item->getId();
                    }
                } else {
                    $data = [];
                    $data['supplier_id'] = $senderId;
                    $data['customer_id'] = $receiverId;
                    $data['status'] = 1;
                    $list = $model->setData($data)->save();
                    $listId = $list->getId();
                }
            } else {
                $senderId = $helper->getCustomerId();
                $collection->addFieldToFilter("supplier_id", $receiverId);
                $collection->addFieldToFilter("customer_id", $senderId);
                if ($collection->getSize()) {
                    foreach ($collection as $item) {
                        $listId = $item->getId();
                    }
                } else {
                    $data = [];
                    $data['supplier_id'] = $receiverId;
                    $data['customer_id'] = $senderId;
                    $data['status'] = 1;
                    $list = $model->setData($data)->save();
                    $listId = $list->getId();
                }
            }
            $this->setListId($listId)->setId($this->getId())->save();
        }
    }
}
