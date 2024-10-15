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
use Webkul\B2BMarketplace\Api\Data\ItemAttachmentInterface;

/**
 * DTO for ItemAttachment entity.
 */
class ItemAttachment extends AbstractExtensibleModel implements ItemAttachmentInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getAttachmentId()
    {
        return $this->getData(ItemAttachmentInterface::ATTACHMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAttachmentId($id)
    {
        return $this->setData(ItemAttachmentInterface::ATTACHMENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteItemId()
    {
        return $this->getData(ItemAttachmentInterface::QUOTE_ITEM_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteItemId($quoteItemId)
    {
        return $this->setData(ItemAttachmentInterface::QUOTE_ITEM_ID, $quoteItemId);
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return $this->getData(ItemAttachmentInterface::FILE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setFileName($name)
    {
        return $this->setData(ItemAttachmentInterface::FILE_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getFilePath()
    {
        return $this->getData(ItemAttachmentInterface::FILE_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setFilePath($path)
    {
        return $this->setData(ItemAttachmentInterface::FILE_PATH, $path);
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
        \Webkul\B2BMarketplace\Api\Data\ItemAttachmentExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
