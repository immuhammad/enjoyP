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
 * MpRmaSystem Reasons interface.
 *
 * @api
 */
interface ReasonsInterface
{
    /**
     * Constants for keys of data array.
     */
    public const ENTITY_ID = 'id';
    public const REASON = 'reason';
    public const STATUS = 'status';

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
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setId($id);

    /**
     * Get REASON.
     *
     * @return string|null
     */
    public function getReason();

    /**
     * Set REASON.
     *
     * @param string $reason
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setReason($reason);

    /**
     * Get STATUS.
     *
     * @return int|null
     */
    public function getStatus();

    /**
     * Set STATUS.
     *
     * @param int $status
     *
     * @return \Webkul\MpRmaSystem\Api\Data\ReasonsInterface
     */
    public function setStatus($status);
}
