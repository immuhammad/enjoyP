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
namespace Webkul\MpVendorAttributeManager\ViewModel;

use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

/**
 * MpVendorAttributeManager Helper View Model
 */
class HelperViewModel implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var MarketplaceHelper
     */
    protected $mpHelper;

    /**
     * Constructor
     *
     * @param MarketplaceHelper $mpHelper
     */
    public function __construct(
        MarketplaceHelper $mpHelper
    ) {
        $this->mpHelper = $mpHelper;
    }

    /**
     * Get Marketplace Helper
     *
     * @return object \Webkul\Marketplace\Helper\Data
     */
    public function getMarketplaceHelper()
    {
        return $this->mpHelper;
    }
}
