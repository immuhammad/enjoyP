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

namespace Webkul\Mpquotesystem\Plugin\Block\Product\Compare;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\Context;
use Magento\Framework\App\Action\Action;
use Webkul\Mpquotesystem\Helper\Data;

/**
 * Catalog products compare block
 */
class ListCompare extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $helper;

    /**
     * Initialize dependencies
     *
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * After plugin of getAttributes function
     *
     * @param \Magento\Catalog\Block\Product\Compare\ListCompare $subject
     * @param array $result
     *
     * @return array
     */
    public function afterGetAttributes(
        \Magento\Catalog\Block\Product\Compare\ListCompare $subject,
        $result
    ) {
        try {
            $comparableArray = [];
            if (!$this->_helper->getQuoteEnabled()) {
                $attributeArray = $result;
                foreach ($attributeArray as $attribute) {
                    if ($attribute->getAttributeCode() == 'min_quote_qty' ||
                    $attribute->getAttributeCode() == 'quote_status') {
                        unset($result['min_quote_qty']);
                        unset($result['quote_status']);
                    } else {
                        array_push($comparableArray, $attribute->getData());
                    }
                }
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }
}
