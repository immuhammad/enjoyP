<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Plugin;

class Currency
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Webkul\SellerSubDomain\Helper\Data $helepr
     */
    public function __construct(
        \Webkul\SellerSubDomain\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param \Magento\Store\Model\Store $subject
     * @param $result
     * @return string
     */
    public function afterGetSwitchCurrencyPostData(
        \Magento\Directory\Block\Currency $subject,
        $result
    ) {
        return $this->_helper->getCurrencyUrl($result);
    }
}
