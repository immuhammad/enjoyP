<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\CustomerData\Rewrite\Checkout;

/**
 * Cart source
 */
class Cart extends \Magento\Checkout\CustomerData\Cart
{
    protected $_b2bQuoteHelper;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Checkout\Model\Cart $checkoutCart,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Checkout\CustomerData\ItemPoolInterface $itemPoolInterface,
        \Magento\Framework\View\LayoutInterface $layout,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper,
        array $data = []
    ) {
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        parent::__construct(
            $checkoutSession,
            $catalogUrl,
            $checkoutCart,
            $checkoutHelper,
            $itemPoolInterface,
            $layout,
            $data
        );
    }
    /**
     * Get array of last added items
     *
     * @return \Magento\Quote\Model\Quote\Item[]
     */
    protected function getRecentItems()
    {
        $items = [];
        if (!$this->getSummaryCount()) {
            return $items;
        }

        foreach (array_reverse($this->getAllQuoteItems()) as $item) {
            if (!$item->getProduct()->isVisibleInSiteVisibility()) {
                $product =  $item->getOptionByCode('product_type') !== null
                    ? $item->getOptionByCode('product_type')->getProduct()
                    : $item->getProduct();

                $products = $this->catalogUrl->getRewriteByProductStore([$product->getId() => $item->getStoreId()]);
                if (!isset($products[$product->getId()])) {
                    if ($this->_b2bQuoteHelper->allowProductInCart($product)) {
                        $items[] = $this->itemPoolInterface->getItemData($item);
                    }
                    continue;
                }
    
                $urlDataObject = new \Magento\Framework\DataObject($products[$product->getId()]);
                $item->getProduct()->setUrlDataObject($urlDataObject);
            }

            $items[] = $this->itemPoolInterface->getItemData($item);
        }

        return $items;
    }
}
