<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Plugin\Config\Model\Config\Structure\Element;

class Section
{
    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    private $_helper;

    public function __construct(
        \Webkul\B2BMarketplace\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterGetLabel(
        \Magento\Config\Model\Config\Structure\Element\Section $subject,
        $result
    ) {
        if ($result == "Marketplace Quotesystem") {
            return __("Quote Configuration");
        }

        return $result;
    }
}
