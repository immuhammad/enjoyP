<?php
/**
 * Webkul
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Plugin\Catalog\Block\Product\View\Options;

class AbstractOptions
{

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper
    ) {
        $this->quoteHelper = $quoteHelper;
    }
    
    /**
     * Plugin to update format price string
     *
     * @param  \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject
     * @param  [string]                                                    $result
     * @return string
     */
    public function afterGetFormatedPrice(
        \Magento\Catalog\Block\Product\View\Options\AbstractOptions $subject,
        $result
    ) {
        try {
            $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
            $product = $subject->getProduct();
            $quoteStatus = $product->getQuoteStatus();
            if (!$showPrice && ($quoteStatus == 1)) {
                return '';
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }
}
