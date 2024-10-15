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
namespace Webkul\MpServiceFee\Plugin;

class LayoutProcessorPlugin
{
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     */
    public function __construct(
        \Webkul\MpServiceFee\Helper\Servicehelper $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * After process plugin
     *
     * @param LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        array $jsLayout
    ) {
        $jsLayout['components']['checkout']
                    ['children']['sidebar']
                    ['children']['summary']
                    ['children']['totals']
                    ['children']['customfee']
                    ['config']['title'] = $this->helper->activeServiceNames();
        return $jsLayout;
    }
}
