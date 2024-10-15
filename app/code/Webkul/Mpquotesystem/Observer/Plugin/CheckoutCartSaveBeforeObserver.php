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

namespace Webkul\Mpquotesystem\Observer\Plugin;

use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelperData;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Mpquotesystem\Helper\Data as QuoteHelperData;

class CheckoutCartSaveBeforeObserver
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
     * @var $quoteHelper
     */
    protected $quoteHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param CheckoutSession                     $checkoutSession
     * @param ManagerInterface                    $messageManager
     * @param MarketplaceHelperData               $marketplaceHelperData
     * @param ProductRepositoryInterface          $productRepository
     * @param QuoteHelperData                     $quoteHelper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        CheckoutSession $checkoutSession,
        ManagerInterface $messageManager,
        MarketplaceHelperData $marketplaceHelperData,
        ProductRepositoryInterface $productRepository,
        QuoteHelperData $quoteHelper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->_messageManager = $messageManager;
        $this->_marketplaceHelperData = $marketplaceHelperData;
        $this->_productRepository = $productRepository;
        $this->quoteHelper = $quoteHelper;
        $this->_request = $request;
    }

    /**
     * Around plugin of execute function
     *
     * @param \Webkul\Marketplace\Observer\CheckoutCartSaveBeforeObserver $subject
     * @param callable                                                    $proceed
     *
     * @return void
     */
    public function aroundExecute(
        \Webkul\Marketplace\Observer\CheckoutCartSaveBeforeObserver $subject,
        callable $proceed
    ) {
        $flag = 1;
        try {
            if ($this->_marketplaceHelperData->getAllowProductLimit()) {
                $items =  $this->_checkoutSession->getQuote()->getAllVisibleItems();
                foreach ($items as $item) {
                    list($qty, $price) = $this->getQuoteSystemQty($item);
                    if ($qty==0) {
                        $qty = $this->getMpItemQty($item);
                    }
                    if (($qty!=0 || $qty!='' || $qty>0) && $qty!=$item->getQty()) {
                        $item->setQty($qty);
                    }
                    if ($price!=0 && $price!='' && $price>0 && $price!=$item->getCustomPrice()) {
                        $item->setCustomPrice($price);
                        $item->setOriginalCustomPrice($price);
                        $item->setRowTotal($price * $qty);
                        $item->getProduct()->setIsSuperMode(true);
                        if ($this->quoteHelper->checkAndUpdateForDiscount($item)) {
                            $item->setNoDiscount(1);
                        } else {
                            $item->setNoDiscount(0);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
        return $this;
    }

    /**
     * GetMpItemQty
     *
     * @param object $item
     * @return int
     */
    protected function getMpItemQty($item)
    {
        $qty = 0;
        $product = $this->_productRepository->getById($item->getProductId());
        $productTypeId = $product['type_id'];
        if ($productTypeId != 'downloadable' && $productTypeId != 'virtual') {
            $mpProductCartLimit = $product['mp_product_cart_limit'];
            if (!$mpProductCartLimit) {
                $mpProductCartLimit = $this->_marketplaceHelperData->getGlobalProductLimitQty();
            }
            if ($item->getQty() > $mpProductCartLimit) {
                $qty = $mpProductCartLimit;
                $productName = "<b>".$item->getName()."</b>";
                $this->_messageManager->addError(
                    __(
                        'Sorry, but you can only add maximum %1 quantity of %2 in this cart.',
                        $mpProductCartLimit,
                        $productName
                    )
                );
            }
        }
        return $qty;
    }

    /**
     * GetQuoteSystemQty
     *
     * @param object $item
     * @return array
     */
    private function getQuoteSystemQty($item)
    {
        $qty = 0;
        $finalPrice = 0;
        $baseCurrencyCode = $this->quoteHelper->getBaseCurrencyCode();
        $currentCurrencyCode = $this->quoteHelper->getCurrentCurrencyCode();
        if ($item->getId() || $item->getItemId()) {
            $price = 0;
            $quoteId = 0;
            $quoteQty = 0;
            $quoteCollection = $this->quoteHelper->getWkQuoteModel()->getCollection()
                ->addFieldToFilter("item_id", $item->getItemId());
            $quoteCurrencyCode = '';
            if ($quoteCollection->getSize()) {
                foreach ($quoteCollection as $quote) {
                    $price = $quote->getQuotePrice();
                    $quoteId = $quote->getEntityId();
                    $quoteQty = $quote->getQuoteQty();
                    $quoteCurrencyCode = $quote->getQuoteCurrencyCode();
                }
            }
            $qty = $quoteQty;
            if ($quoteId != 0 && $quoteQty!=$item->getQty()) {
                $flag = 1;
                $this->_messageManager->addNotice(
                    __(
                        "You can't edit quote items"
                    )
                );
            }
            $priceOne = $this->quoteHelper->getwkconvertCurrency(
                $baseCurrencyCode,
                $currentCurrencyCode,
                $price,
                $quoteCurrencyCode
            );
            if ($priceOne!=$item->getCustomPrice()) {
                $finalPrice = $priceOne;
            }
        } else {
            $params = $this->_request->getParams();
            if (is_array($params) && array_key_exists('quote_id', $params) && $params['quote_id']>0) {
                $quoteCollection = $this->quoteHelper->getWkQuoteModel()->load($params['quote_id']);
                $qty = $quoteCollection->getQuoteQty();
                $price = $quoteCollection->getQuotePrice();
                $quoteCurrencyCode = $quoteCollection->getQuoteCurrencyCode();
                $priceOne = $this->quoteHelper->getwkconvertCurrency(
                    $baseCurrencyCode,
                    $currentCurrencyCode,
                    $price,
                    $quoteCurrencyCode
                );
                $finalPrice = $priceOne;
            }
        }
        return [$qty, $finalPrice];
    }
}
