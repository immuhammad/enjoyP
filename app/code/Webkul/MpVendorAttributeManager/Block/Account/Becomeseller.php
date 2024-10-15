<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block\Account;

class Becomeseller extends \Webkul\Marketplace\Block\Account\Becomeseller
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * Function get MpHelper
     *
     * @return object
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
    }

    /**
     * Function get Helper
     *
     * @return object
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Function get JsonHelper
     *
     * @return object
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
