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

namespace Webkul\Mpquotesystem\CustomerData\Rewrite;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class LastOrderedItems extends \Magento\Sales\CustomerData\LastOrderedItems
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Magento\Sales\Model\Order\Config                          $orderConfig
     * @param \Magento\Customer\Model\Session                            $customerSession
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface       $stockRegistry
     * @param \Magento\Store\Model\StoreManagerInterface                 $storeManager
     * @param ProductRepositoryInterface                                 $productRepository
     * @param \Webkul\Mpquotesystem\Helper\Data                          $quoteHelper
     * @param LoggerInterface|null                                       $logger
     */
    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper,
        LoggerInterface $logger
    ) {
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->quoteHelper = $quoteHelper;
        $this->logger = $logger;
        parent::__construct(
            $orderCollectionFactory,
            $orderConfig,
            $customerSession,
            $stockRegistry,
            $storeManager,
            $productRepository,
            $logger
        );
    }

    /**
     * Get list of last ordered products
     *
     * @return array
     */
    protected function getItems()
    {
        $items = [];
        $order = $this->getLastOrder();
        $limit = self::SIDEBAR_ORDER_LIMIT;
        $status = $this->quoteHelper->getModuleStatus();
        if ($status) {
            if ($order) {
                $website = $this->_storeManager->getStore()->getWebsiteId();
                /**
                 * @var \Magento\Sales\Model\Order\Item $item
                */
                foreach ($order->getParentItemsRandomCollection($limit) as $item) {
                    /**
                     * @var \Magento\Catalog\Model\Product $product
                    */
                    try {
                        $cartflag = false;
                        $product = $this->productRepository->getById(
                            $item->getProductId(),
                            false,
                            $this->_storeManager->getStore()->getId()
                        );
                        $status = $product->getQuoteStatus();
                        $showAddToCart = (int)$this->quoteHelper->getConfigAddToCart();
                        if (($status == 1) && !$showAddToCart) {
                            $cartflag = true;
                        }
                    } catch (NoSuchEntityException $noEntityException) {
                        $this->logger->critical($noEntityException);
                        continue;
                    }
                    if (isset($product) && in_array($website, $product->getWebsiteIds())) {
                        $url = $product->isVisibleInSiteVisibility() ? $product->getProductUrl() : null;
                        $items[] = [
                            'id' => $item->getId(),
                            'name' => $item->getName(),
                            'url' => $url,
                            'is_saleable' => $cartflag ? !$cartflag : $this->isItemAvailableForReorder($item),
                        ];
                    }
                }
                return $items;
            }
        }
    }
}
