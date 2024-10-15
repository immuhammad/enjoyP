<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Block;

use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

/**
 * Webkul Mpmembership Seller Navigation Block
 */
class Navigation extends \Magento\Framework\View\Element\Template
{
    /**
     * @var $mphelper Webkul\Marketplace\Helper\Data
     */
    protected $mphelper;

    /**
     * @var $mphelper Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpmembership\Helper\Data $helper,
        array $data = []
    ) {
        $this->mphelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * checkIsPartner used to check current user is seller or not
     *
     * @return int
     */
    public function checkIsPartner()
    {
        try {
            return $this->mphelper->isSeller();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Navigation_checkIsPartner Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        try {
            return $this->getRequest()->isSecure();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Navigation_getIsSecure Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * checkVendorFeeOption
     *
     * @return boolean
     */
    public function checkVendorFeeOption()
    {
        try {
            $fee = $this->helper->getConfigFeeAppliedFor();
            if ($fee == Feeapplied::PER_VENDOR) {
                return true;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Navigation_checkVendorFeeOption Exception : ".$e->getMessage()
            );
        }
    }
    /**
     * Return Marketplace Helper object
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function mpHelper()
    {
        return $this->mphelper;
    }
    /**
     * Give the current url of recently viewed page
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
}
