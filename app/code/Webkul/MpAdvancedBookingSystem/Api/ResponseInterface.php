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
namespace Webkul\MpAdvancedBookingSystem\Api;

interface ResponseInterface
{
    public const RESPONSE_DATA  = 'response_data';
    public const SUCCESS = 'success';
    public const MESSAGE = 'message';

    /**
     * Get Success
     *
     * @return boolean|null
     */
    public function getSuccess();
     
    /**
     * Set Success
     *
     * @param boolean $success
     * @return boolean
     */
    public function setsuccess($success);

    /**
     * Get response Data
     *
     * @return string|null
     */
    public function getResponseData();
     
    /**
     * Set response Data
     *
     * @param string $responseData
     * @return string
     */
    public function setResponseData($responseData);

    /**
     * Get message
     *
     * @return string|null
     */
    public function getMessage();
     
    /**
     * Set Message
     *
     * @param string $messgae
     * @return string
     */
    public function setMessage($messgae);
}
