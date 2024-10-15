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
namespace Webkul\MpAdvancedBookingSystem\Model\Api;

use Webkul\MpAdvancedBookingSystem\Api\ResponseInterface;

class Response extends \Magento\Framework\DataObject implements ResponseInterface
{
    /**
     * Get Success
     *
     * @return boolean|null
     */
    public function getSuccess()
    {
        return $this->getData(self::SUCCESS);
    }
     
    /**
     * Set Success
     *
     * @param boolean $success
     * @return boolean
     */
    public function setSuccess($success)
    {
        return $this->setData(self::SUCCESS, $success);
    }

    /**
     * Get Message
     *
     * @return string|null
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }
     
    /**
     * Set Message
     *
     * @param string $messgae
     * @return string
     */
    public function setMessage($messgae)
    {
        return $this->setData(self::MESSAGE, $messgae);
    }

    /**
     * Get Response Data
     *
     * @return string|null
     */
    public function getResponseData()
    {
        return $this->getData(self::RESPONSE_DATA);
    }
     
    /**
     * Set Response Data
     *
     * @param string $data
     * @return string
     */
    public function setResponseData($data)
    {
        return $this->setData(self::RESPONSE_DATA, $data);
    }
}
