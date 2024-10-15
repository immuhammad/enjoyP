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

interface AttachmentInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID     = 'id';
    const MESSAGE_ID    = 'msg_id';
    const TYPE          = 'type';
    const FILE_NAME     = 'file_name';
    const VALUE         = 'value';
    const CREATED_AT    = 'created_at';
    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\B2BMarketplace\Api\Data\AttachmentInterface
     */
    public function setId($id);

    /**
     * Get message ID.
     *
     * @return int
     */
    public function getMessageId();

    /**
     * Set message ID.
     *
     * @param int $messageId
     * @return $this
     */
    public function setMessageId($messageId);

    /**
     * Set Type ID.
     *
     * @param int $type
     * @return $this
     */
    public function setType($type);

    /**
     * Get Type ID.
     *
     * @return int
     */
    public function getType();

    /**
     * Get file name.
     *
     * @return string
     */
    public function getFileName();

    /**
     * Set file name.
     *
     * @param string $fileName
     * @return $this
     */
    public function setFileName($fileName);

    /**
     * Get file path.
     *
     * @return string
     */
    public function getValue();

    /**
     * Set file path.
     *
     * @param string $path
     * @return $this
     */
    public function setValue($path);

    /**
     * Get created date.
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * Set created date.
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Webkul\B2BMarketplace\Api\Data\AttachmentExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Webkul\B2BMarketplace\Api\Data\AttachmentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\B2BMarketplace\Api\Data\AttachmentExtensionInterface $extensionAttributes
    );
}
