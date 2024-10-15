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

use Magento\Framework\View\Element\Html\Link;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Http\Context as HttpContext;
use Webkul\MpVendorAttributeManager\Model\Url;
use Magento\Customer\Model\Context as CustomerContext;
use Webkul\MpVendorAttributeManager\Helper\Data as VendorAttributeHelper;

class RegisterLink extends Link
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\Url
     */
    private $vendorUrl;

    /**
     * @var Webkul\MpVendorAttributeManager\Helper\Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param HttpContext $httpContext
     * @param Url $vendorUrl
     * @param VendorAttributeHelper $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        HttpContext $httpContext,
        Url $vendorUrl,
        VendorAttributeHelper $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->httpContext = $httpContext;
        $this->vendorUrl = $vendorUrl;
        $this->helper = $helper;
    }

    /**
     * Get Vendor URL
     *
     * @return string
     */
    public function getHref()
    {
        return $this->vendorUrl->getVendorUrl();
    }

    /**
     * Convert to HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->httpContext->getValue(CustomerContext::CONTEXT_AUTH)
            || $this->helper->isB2BMarketplaceInstalled()
        ) {
            return '';
        }
        return parent::_toHtml();
    }
}
