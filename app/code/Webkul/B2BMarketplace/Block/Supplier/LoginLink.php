<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Block\Supplier;

use Magento\Customer\Model\Context;

class LoginLink extends \Magento\Framework\View\Element\Html\Link
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        \Webkul\B2BMarketplace\Helper\Data $b2bHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->_b2bHelper = $b2bHelper;
    }

    protected function _toHtml()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            if ($this->_b2bHelper->isLoggedInSupplier()) {
                return '<li><a href="'.
                $this->getUrl("b2bmarketplace/supplier/account").'">'.
                __('Supplier Dashboard').'</a></li>';
            } else {
                return '';
            }
        }

        return parent::_toHtml();
    }
}
