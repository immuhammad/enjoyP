<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Plugin\Catalog\Helper\Product;

use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Catalog\Helper\Product\Configuration as ProductConfiguration;

class Configuration
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    private $helper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    
    /**
     * Around plugin of getCustomOptions
     *
     * @param ProductConfiguration $subject
     * @param \Closure $proceed
     * @param ItemInterface $item
     *
     * @return void
     */
    public function aroundGetCustomOptions(
        ProductConfiguration $subject,
        \Closure $proceed,
        ItemInterface $item
    ) {
        $result = $proceed($item);
        return $result;
    }
}
