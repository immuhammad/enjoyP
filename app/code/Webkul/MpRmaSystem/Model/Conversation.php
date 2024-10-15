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

use Webkul\MpRmaSystem\Api\Data\ConversationInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * MpRmaSystem Conversation Model.
 *
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Conversation _getResource()
 * @method \Webkul\MpRmaSystem\Model\ResourceModel\Conversation getResource()
 */
class Conversation extends AbstractModel implements ConversationInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    public const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * MpRmaSystem Conversation cache tag.
     */
    public const CACHE_TAG = 'mprmasystem_conversation';

    /**
     * @var string
     */
    protected $_cacheTag = 'mprmasystem_conversation';

    /**
     * @var string
     */
    protected $_eventPrefix = 'mprmasystem_conversation';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init(\Webkul\MpRmaSystem\Model\ResourceModel\Conversation::class);
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
            return $this->noRouteConversation();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Conversation.
     *
     * @return \Webkul\MpRmaSystem\Model\Conversation
     */
    public function noRouteConversation()
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
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get RMA_ID.
     *
     * @return int|null
     */
    public function getRmaId()
    {
        return parent::getData(self::RMA_ID);
    }

    /**
     * Set RMA_ID.
     *
     * @param int $rmaId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setRmaId($rmaId)
    {
        return $this->setData(self::RMA_ID, $rmaId);
    }

    /**
     * Get SENDER_TYPE.
     *
     * @return int|null
     */
    public function getSenderType()
    {
        return parent::getData(self::SENDER_TYPE);
    }

    /**
     * Set SENDER_TYPE.
     *
     * @param int $senderType
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setSenderType($senderType)
    {
        return $this->setData(self::SENDER_TYPE, $senderType);
    }

    /**
     * Get MESSAGE.
     *
     * @return string|null
     */
    public function getMessage()
    {
        return parent::getData(self::MESSAGE);
    }

    /**
     * Set MESSAGE.
     *
     * @param string $message
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * Get CREATED_TIME.
     *
     * @return string|null
     */
    public function getCreatedTime()
    {
        return parent::getData(self::CREATED_TIME);
    }

    /**
     * Set CREATED_TIME.
     *
     * @param string $createdTime
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setCreatedTime($createdTime)
    {
        return $this->setData(self::CREATED_TIME, $createdTime);
    }
}
