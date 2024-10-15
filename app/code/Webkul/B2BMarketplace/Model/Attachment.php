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

use Magento\Framework\Model\AbstractExtensibleModel;
use Webkul\B2BMarketplace\Api\Data\AttachmentInterface;

class Attachment extends AbstractExtensibleModel implements AttachmentInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Attachment cache tag.
     */
    const CACHE_TAG = 'b2b_attachment';

    /**
     * @var string
     */
    protected $_cacheTag = 'b2b_attachment';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'b2b_attachment';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\B2BMarketplace\Model\ResourceModel\Attachment');
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
     * Load No-Route Attachment.
     *
     * @return \Webkul\B2BMarketplace\Model\Attachment
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
     * @inheritdoc
     */
    public function getMessageId()
    {
        return $this->getData(AttachmentInterface::MESSAGE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setMessageId($messageId)
    {
        return $this->setData(AttachmentInterface::MESSAGE_ID, $messageId);
    }

    /**
     * @inheritdoc
     */
    public function getType()
    {
        return $this->getData(AttachmentInterface::TYPE);
    }

    /**
     * @inheritdoc
     */
    public function setType($type)
    {
        return $this->setData(AttachmentInterface::TYPE, $type);
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return $this->getData(AttachmentInterface::FILE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setFileName($name)
    {
        return $this->setData(AttachmentInterface::FILE_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getValue()
    {
        return $this->getData(AttachmentInterface::VALUE);
    }

    /**
     * @inheritdoc
     */
    public function setValue($path)
    {
        return $this->setData(AttachmentInterface::VALUE, $path);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->getData(AttachmentInterface::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(AttachmentInterface::CREATED_AT, $createdAt);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Webkul\B2BMarketplace\Api\Data\AttachmentExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }

    /**
     * Saving product type related data and init index
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function afterSave()
    {
        $result = parent::afterSave();
        return $result;
    }
}
