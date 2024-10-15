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

use Magento\Catalog\Model\Product;
use Webkul\Mpquotesystem\Helper\Data;

class Mpquotesystem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product $product
     * @param Data $helper
     * @param MpQuoteConfig $mpQuoteConfig
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        Data $helper,
        MpQuoteConfig $mpQuoteConfig,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollectionFactory,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->mpQuoteConfig = $mpQuoteConfig;
        $this->helper = $helper;
        $this->_product = $product;
        $this->_mpquote = $mpquotes;
        $this->_mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve current order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function isQuoteProductOrder()
    {
        $currentOrder = $this->_coreRegistry->registry('current_order');
        $orderItems = $currentOrder->getAllItems();
        foreach ($orderItems as $item) {
            $quoteCollection = $this->helper->getWkQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getQuoteItemId());
            if ($quoteCollection->getSize()) {
                return [true, $item->getItemId()];
            }
        }
        return [false];
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
     * GetProduct
     *
     * @return object
     */
    public function getProduct()
    {
        return $this->_product;
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
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Return default value of Minimum Quote Quantity
     *
     * @return minimum quote quanitity
     */
    public function getMinQuoteQty()
    {
        $minQuoteQty = $this->mpQuoteConfig->getConfigData()->getMinQty();
        if ($minQuoteQty) {
            return $minQuoteQty;
        }
        if ($this->helper->getGlobalMinQty() == 0) {
            return 0;
        }
        return $this->helper->getGlobalMinQty();
    }

    /**
     * Count total quote notifications.
     *
     * @return int
     */
    public function getQuoteNotificationCount()
    {
        $customerId = $this->helper->getCustomerId();
        $collection = $this->_mpProductCollectionFactory->create()
            ->addFieldToFilter('seller_id', $customerId);
        $sellerProductId = [];
        if ($collection->getSize()) {
            foreach ($collection as $sellerProduct) {
                $sellerProductId[$sellerProduct->getMageproductId()] = $sellerProduct->getMageproductId();
            }
        }
        $quoteCollection = $this->_mpquote->create()
        ->getCollection()
        ->addFieldToFilter('seller_pending_notification', ['eq' => 1]);
        $quoteProductId = [];
        if ($quoteCollection->getSize()) {
            foreach ($quoteCollection as $quoteProduct) {
                $quoteProductId[] = $quoteProduct->getProductId();
            }
        }
        $count = 0;
        if (!empty($quoteProductId)) {
            foreach ($quoteProductId as $key => $value) {
                if (array_key_exists($value, $sellerProductId)) {
                    $count++;
                }
            }
        }
        return $count;
    }

    /**
     * Check quote is in cart or not
     *
     * @return bool
     */
    public function isQuoteProductInCart()
    {
        $session = $this->helper->getCheckoutSession();
        foreach ($session->getQuote()->getAllItems() as $item) {
            if ($item->getParentItemId() === null && $item->getItemId() > 0) {
                $quoteCollection = $this->helper->getWkQuoteModel()->getCollection()
                    ->addFieldToFilter("item_id", $item->getItemId());
                if ($quoteCollection->getSize()) {
                    return true;
                }
            }
        }
        return false;
    }
}
