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
namespace Webkul\MpAdvancedBookingSystem\Helper;

use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul MpAdvancedBookingSystem Helper MobikulApi.
 */
class MobikulApi extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private $resultFactory;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }

    /**
     * Return json response.
     *
     * @param array  $responseContent response content
     * @param string $responseCode    response code
     * @param string $token           token
     *
     * @return ResultFactory $resultJson
     */
    public function getJsonResponse($responseContent = [], $responseCode = "", $token = "")
    {
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        if ($responseCode == 304) {
            $resultJson->setHttpResponseCode(304);
            return $resultJson;
        } elseif ($responseCode == 401) {
            $resultJson->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_UNAUTHORIZED);
        }
        if ($token != "") {
            $resultJson->setHeader("token", $token, true);
        }
        $resultJson->setData($responseContent);
        // $this->helper->log($responseContent, "logResponse", $this->wholeData);
        return $resultJson;
    }
}
