<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Interface for b2b quote item attachment.
 */
interface ItemAttachmentInterface extends ExtensibleDataInterface
{
    /**#@+
     * Constants
     */
    const ATTACHMENT_ID = 'attachment_id';
    const QUOTE_ITEM_ID = 'quote_item_id';
    const FILE_NAME     = 'file_name';
    const FILE_PATH     = 'file_path';
    /**#@-*/

    /**
     * Get attachment ID.
     *
     * @return int
     */
    public function getAttachmentId();

    /**
     * Set attachment ID.
     *
     * @param int $id
     * @return $this
     */
    public function setAttachmentId($id);

    /**
     * Get quote item ID.
     *
     * @return int
     */
    public function getQuoteItemId();

    /**
     * Set quote item ID.
     *
     * @param int $itemId
     * @return $this
     */
    public function setQuoteItemId($itemId);

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set file name.
     *
     * @param string $name
     * @return $this
     */
    public function setFileName($name);

    /**
     * Get file path.
     *
     * @return string
     */
    public function getFilePath();

    /**
     * Set file path.
     *
     * @param string $path
     * @return $this
     */
    public function setFilePath($path);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Webkul\B2BMarketplace\Api\Data\ItemAttachmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Webkul\B2BMarketplace\Api\Data\ItemAttachmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\B2BMarketplace\Api\Data\ItemAttachmentExtensionInterface $extensionAttributes
    );
}
