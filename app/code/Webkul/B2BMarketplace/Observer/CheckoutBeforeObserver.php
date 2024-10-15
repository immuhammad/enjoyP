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

namespace Webkul\B2BMarketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * Webkul B2BMarketplace CheckoutBeforeObserver Observer.
 */
class CheckoutBeforeObserver implements ObserverInterface
{
    /**
     * @var CheckoutSession
     */
    protected $_checkoutSession;

    /**
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     * @var MarketplaceHelperData
     */
    protected $_marketplaceHelperData;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param CheckoutSession                $checkoutSession
     * @param ManagerInterface               $messageManager
     * @param MarketplaceHelperData          $marketplaceHelperData
     * @param ProductRepositoryInterface     $productRepository
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        MarketplaceHelperData $marketplaceHelperData,
        ProductRepositoryInterface $productRepository
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->_productRepository = $productRepository;
    }

    /**
     * Checkout event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();
            foreach ($items as $item) {
                $sellerProductDataColl = $this->_marketplaceHelperData->getSellerProductDataByProductId(
                    $item->getProductId()
                );
                if (count($sellerProductDataColl)) {
                    $product = $this->_productRepository->getById($item->getProductId());
                    $productTypeId = $product['type_id'];
                    if ($productTypeId != 'downloadable' && $productTypeId != 'virtual') {
                        $allowedMinQty = $product['allowed_min_qty_to_order'];
                        if (!$allowedMinQty) {
                            $allowedMinQty = 1;
                        }
                        if ($item->getQty() < $allowedMinQty) {
                            $item->setQty($allowedMinQty);
                            $productName = "<b>".$item->getName()."</b>";
                            $this->_messageManager->addError(
                                __(
                                    'Sorry, but minimum allowed quantity for "%1" is %2.',
                                    $productName,
                                    $allowedMinQty
                                )
                            );
                            $this->_checkoutSession->getQuote()->setHasError(true);
                            $this->_checkoutSession->setItemMinQtyStatus(true);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}
