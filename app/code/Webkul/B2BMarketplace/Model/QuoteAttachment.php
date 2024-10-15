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
use Webkul\B2BMarketplace\Api\Data\QuoteAttachmentInterface;

/**
 * DTO for QuoteAttachment entity.
 */
class QuoteAttachment extends AbstractExtensibleModel implements QuoteAttachmentInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getAttachmentId()
    {
        return $this->getData(QuoteAttachmentInterface::ATTACHMENT_ID);
    }

    /**
     * @inheritdoc
     */
    public function setAttachmentId($id)
    {
        return $this->setData(QuoteAttachmentInterface::ATTACHMENT_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getQuoteId()
    {
        return $this->getData(QuoteAttachmentInterface::QUOTE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setQuoteId($quote_id)
    {
        return $this->setData(QuoteAttachmentInterface::QUOTE_ID, $quote_id);
    }

    /**
     * @inheritdoc
     */
    public function getFileName()
    {
        return $this->getData(QuoteAttachmentInterface::FILE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setFileName($name)
    {
        return $this->setData(QuoteAttachmentInterface::FILE_NAME, $name);
    }

    /**
     * @inheritdoc
     */
    public function getFilePath()
    {
        return $this->getData(QuoteAttachmentInterface::FILE_PATH);
    }

    /**
     * @inheritdoc
     */
    public function setFilePath($path)
    {
        return $this->setData(QuoteAttachmentInterface::FILE_PATH, $path);
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
        \Webkul\B2BMarketplace\Api\Data\QuoteAttachmentExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
