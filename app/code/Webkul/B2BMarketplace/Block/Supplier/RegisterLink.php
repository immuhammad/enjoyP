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

class RegisterLink extends \Magento\Framework\View\Element\Html\Link
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
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
    }

    public function getHref()
    {
        return $this->getUrl("b2bmarketplace/supplier/create");
    }

    protected function _toHtml()
    {
        if ($this->httpContext->getValue(Context::CONTEXT_AUTH)) {
            return '';
        }

        return parent::_toHtml();
    }
}
