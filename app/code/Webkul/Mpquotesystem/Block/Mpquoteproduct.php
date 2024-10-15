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

namespace Webkul\Mpquotesystem\Block;

use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Catalog\Model\Product;
use Webkul\Mpquotesystem\Helper\Data;

class Mpquoteproduct extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Product
     */
    protected $_product = null;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_session;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $session
     * @param Data                                             $helper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        Customer $customer,
        \Magento\Customer\Model\Session $session,
        Data $helper,
        array $data = []
    ) {
        $this->_customer = $customer;
        $this->_session = $session;
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get Product which is saved in registry
     *
     * @return Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = $this->_coreRegistry->registry('product');
        }
        return $this->_product->setStore($this->_helper->getStore());
    }

    /**
     * Use to get current url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    /**
     * GetIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }
    
    /**
     * Check quote status
     *
     * @param object $product
     * @return array
     */
    public function checkQuoteStatus($product)
    {
         return $this->_helper->checkProductHasQuote($product);
    }

    /**
     * Get object of helper class
     *
     * @return void
     */
    public function getHelper()
    {
        return $this->_helper;
    }
}
