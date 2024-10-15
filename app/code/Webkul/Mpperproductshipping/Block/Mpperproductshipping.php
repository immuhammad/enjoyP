<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpperproductshipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Mpperproductshipping\Block;

use Magento\Customer\Model\Session;
use Magento\Framework\UrlInterface;
use Magento\Catalog\Model\Product;
use Webkul\Mpperproductshipping\Helper\Data;

class Mpperproductshipping extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        Data $helper,
        array $data = []
    ) {
        $this->_product = $product;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    
    public function getProduct()
    {
        return $this->_product;
    }

    /**
     * Get Helper Class Data
     */
    public function getHelperClass()
    {
        return $this->helper;
    }

    /**
     * Get Request Params
     */
    public function getParams()
    {
        return $this->getRequest()->getParams();
    }
}
