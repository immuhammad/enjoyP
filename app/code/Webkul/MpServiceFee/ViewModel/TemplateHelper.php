<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;

class TemplateHelper implements ArgumentInterface
{

    /**
     * @var \Webkul\MpServiceFee\Helper\Data $currHelper
     */
    protected $currHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Servicehelper $helper
     */
    protected $helper;

    /**
     * Class constructor
     *
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $currHelper
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Orders $orderHelper
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Webkul\MpServiceFee\Helper\Servicehelper $currHelper,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->helper = $helper;
        $this->currHelper = $currHelper;
        $this->orderHelper = $orderHelper;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * Get mp Order helper obj
     *
     * @return mpOrderHelper
     */
    public function getMpOrderHelper()
    {
        return $this->orderHelper;
    }

    /**
     * Get marketplace helper object
     *
     * @return marketplaceHelper
     */
    public function getMpHelper()
    {
        return $this->helper;
    }
    
    /**
     * Get current helper obj
     *
     * @return currentHelper
     */
    public function getCurrentHelper()
    {
        return $this->currHelper;
    }

    /**
     * JsonHelper function
     *
     * @return void
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }
}
