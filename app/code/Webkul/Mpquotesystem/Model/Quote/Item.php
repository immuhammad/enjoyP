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

namespace Webkul\Mpquotesystem\Model\Quote;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Quote\Model\Quote\Item\Option\ComparatorInterface;

class Item extends \Magento\Quote\Model\Quote\Item
{
    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Sales\Model\Status\ListFactory $statusListFactory
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory
     * @param \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ComparatorInterface|null $itemOptionComparator
     * @param \Webkul\Mpquotesystem\Helper\Data|null $quoteHelper
     * @param \Magento\Framework\App\Request\Http|null $request
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Sales\Model\Status\ListFactory $statusListFactory,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Quote\Model\Quote\Item\OptionFactory $itemOptionFactory,
        \Magento\Quote\Model\Quote\Item\Compare $quoteItemCompare,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ?ComparatorInterface $itemOptionComparator = null,
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper = null,
        \Magento\Framework\App\Request\Http $request = null,
    ) {
        $this->quoteHelper = $quoteHelper
            ?: ObjectManager::getInstance()->get(\Webkul\Mpquotesystem\Helper\Data::class);
        $this->request = $request
            ?: ObjectManager::getInstance()->get(\Magento\Framework\App\Request\Http::class);
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $productRepository,
            $priceCurrency,
            $statusListFactory,
            $localeFormat,
            $itemOptionFactory,
            $quoteItemCompare,
            $stockRegistry,
            $resource,
            $resourceCollection,
            $data,
            $serializer,
            $itemOptionComparator
        );
    }

    /**
     * RepresentProduct
     *
     * @param object $product
     * @return void
     */
    public function representProduct($product)
    {
        $itemProduct = $this->getProduct();
        if (!$product || $itemProduct->getId() != $product->getId()) {
            return false;
        }
        $stickWithinParent = $product->getStickWithinParent();
        if ($stickWithinParent) {
            if ($this->getParentItem() !== $stickWithinParent) {
                return false;
            }
        }
        $itemOptions = $this->getOptionsByCode();
        $productOptions = $product->getCustomOptions();

        if (!$this->compareOptions($itemOptions, $productOptions)) {
            return false;
        }
        if (!$this->compareOptions($productOptions, $itemOptions)) {
            return false;
        }

        $helper = $this->quoteHelper;
        $quoteItemCheck = $this->checkQuoteItem($this, $helper);
        
        if (!$quoteItemCheck) {
            return false;
        }

        $params = $this->request->getParams();
        $quoteItems = $helper->getCheckoutSession()->getQuote()->getAllItems();
        if (!empty($quoteItems)) {
            foreach ($quoteItems as $item) {
                if ($item->getParentItem()) {
                    continue;
                }
                if (array_key_exists('product', $params) && $item->getProductId()==$params['product']) {
                    return (bool)(!($helper->checkQuoteProductinItem($item)));
                }
            }
        }
        return true;
    }

    /**
     * Check Quote Item
     *
     * @param object $obj
     * @param object $helper
     *
     * @return boolean
     */
    public function checkQuoteItem($obj, $helper)
    {
        if ($quoteId = $helper->isQuoteItem($obj)) {
            if ($quoteId!=0) {
                return false;
            }
        }
        return true;
    }
}
