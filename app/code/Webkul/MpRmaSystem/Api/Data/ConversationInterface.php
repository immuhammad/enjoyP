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
namespace Webkul\MpRmaSystem\Api\Data;

/**
 * MpRmaSystem Conversation interface.
 *
 * @api
 */
interface ConversationInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'id';
    public const RMA_ID = 'rma_id';
    public const SENDER_TYPE = 'sender_type';
    public const MESSAGE = 'message';
    public const CREATED_TIME = 'created_time';
    
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
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setId($id);

    /**
     * Get RMA_ID.
     *
     * @return int|null
     */
    public function getRmaId();

    /**
     * Set RMA_ID.
     *
     * @param int $rmaId
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setRmaId($rmaId);

    /**
     * Get SENDER_TYPE.
     *
     * @return int|null
     */
    public function getSenderType();

    /**
     * Set SENDER_TYPE.
     *
     * @param int $senderType
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setSenderType($senderType);

    /**
     * Get MESSAGE.
     *
     * @return string|null
     */
    public function getMessage();

    /**
     * Set MESSAGE.
     *
     * @param string $message
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setMessage($message);

    /**
     * Get CREATED_TIME.
     *
     * @return string|null
     */
    public function getCreatedTime();

    /**
     * Set CREATED_TIME.
     *
     * @param string $createdTime
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ConversationInterface
     */
    public function setCreatedTime($createdTime);
}
