<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Helper;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;

/**
 * Service fees helper
 */
class Servicehelper extends \Magento\Framework\App\Helper\AbstractHelper implements ArgumentInterface
{
    /**
     * Constant for enable services
     */
    public const SERVICE_ENABLE = 1;

    /**
     * Constant for disable services
     */
    public const SERVICE_DISABLE = 0;

    /**
     * Constant for fixed service type
     */
    public const SERVICE_TYPE_FIXED = 0;

    /**
     * Constant for percentage service type
     */
    public const SERVICE_TYPE_PERCENTAGE = 1;

    /**
     * enabled service fees
     **/
    public const WK_SERVICE_FEE_ENABLED = "webkul_service_fee/service_fee_settings/enable_disable_service_fee";

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var AttributesListFactory
     */
    protected $modelFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    protected $_directoryHelper;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory
     * @param \Magento\Directory\Model\Currency $currency
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Directory\Helper\Data $directoryHelper
     * @param SellerCollection $sellerCollectionFactory
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\MpServiceFee\Logger\Logger $logger
     * @param \Webkul\Marketplace\Model\Product $mpModel
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Helper\Data $directoryHelper,
        SellerCollection $sellerCollectionFactory,
        \Magento\Checkout\Model\Session $cart,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpServiceFee\Logger\Logger $logger,
        \Webkul\Marketplace\Model\Product $mpModel,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->modelFactory = $modelFactory;
        $this->_currency = $currency;
        $this->_storeManager = $storeManager;
        $this->_priceCurrency = $priceCurrency;
        $this->mpHelper = $mpHelper;
        $this->quoteFactory = $quoteFactory;
        $this->_sellerCollectionFactory = $sellerCollectionFactory;
        $this->_directoryHelper = $directoryHelper;
        $this->_messageManager = $messageManager;
        $this->mpModel = $mpModel;
        $this->cart = $cart;
        $this->logger = $logger;
    }

    /**
     * Checks if module is enable
     *
     * @return boolean
     */
    public function isModuleEnable()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        return $this->_scopeConfig->getValue(self::WK_SERVICE_FEE_ENABLED, $storeScope);
    }

    /**
     * Loads particular data in model
     *
     * @param object $modelObject
     * @param int $entity_id
     * @return object
     */
    public function loadData($modelObject, $entity_id)
    {
        return $modelObject->load($entity_id);
    }

    /**
     * Performs save operation on model object
     *
     * @param object $modelObject
     * @param array|NULL $data
     * @return void
     */
    public function saveData($modelObject, $data = null)
    {
        if (empty($data)) {
            $modelObject->save();
        } else {
            $modelObject->setData($data)->save();
        }
    }

    /**
     * Performs delete operation on model object
     *
     * @param object $dataObject
     * @return void
     */
    public function deleteData($dataObject)
    {
        $dataObject->delete();
    }

    /**
     * Active service names
     *
     * @param int $sellerId
     * @return void
     */
    public function activeServiceNames($sellerId = null)
    {
        $serviceNames = [];
        $symbol = $this->getCurrentCurrencySymbol();
        $checkSellerIdsInCart = $this->checkSellerProductsInCart();
        $serviceCollection = $this->modelFactory->create()->getCollection();
        if ($sellerId != null && $sellerId >= 0) {
            $serviceCollection->addFieldToFilter('service_status', ['eq' => self::SERVICE_ENABLE]);
            $serviceCollection->addFieldToFilter("seller_id", ['eq' => $sellerId]);
        } else {
            $serviceCollection->addFieldToFilter('service_status', ['eq' => self::SERVICE_ENABLE]);
            $serviceCollection->addFieldToFilter("seller_id", ['in' => array_keys($checkSellerIdsInCart)]);
        }
        if ($serviceCollection->getSize() > 0) {
            $currentCurrency = $this->getCurrentCurrencyCode();
            $baseCurrency = $this->getBaseCurrencyCode();
            foreach ($serviceCollection as $activeServices) {
                if ($activeServices->getServiceType() == self::SERVICE_TYPE_FIXED) {
                    array_push(
                        $serviceNames,
                        $activeServices->getServiceTitle() . " - " . $symbol .
                        $this->convertCurrency($activeServices->getServiceValue(), $currentCurrency, $baseCurrency)
                    );
                } else {
                    array_push(
                        $serviceNames,
                        $activeServices->getServiceTitle() . " - " . $activeServices->getServiceValue() . "%"
                    );
                }
            }
        }
        if (empty($serviceNames)) {
            return "";
        }
        return "Service Fees (" . implode(' , ', $serviceNames) . ")";
    }

    /**
     * Returns total fees applied on an order
     *
     * @param object $quote
     * @return float
     */
    public function getTotalFees($quote)
    {
        try {
            $totalFees = 0;
            $totalItemCount = 0;
            $checkSellerIdsInCart = $this->checkSellerProductsInCart();

            if ($quote->getSubtotal() == 0) {
                return $totalFees;
            }
            $quoteId = $this->cart->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $checktotalFees = $totalFees;
                    $sellerId = $this->getSellerId($item->getProductId());
                    $rowTotal = $item->getRowTotal();
                    $serviceCollection = $this->modelFactory->create()->getCollection();
                    $getServiceCollection = $serviceCollection->addFieldToFilter('service_status', ['eq' => 1])
                        ->addFieldToFilter("seller_id", ['eq' => $sellerId]);
                    
                    $sellerserviceFee = "";
                    $cartServiceFeeArray = [];
                    $symbol = $this->getCurrentCurrencySymbol();

                    $currentCurrency = $this->getCurrentCurrencyCode();
                    $baseCurrency = $this->getBaseCurrencyCode();
                    foreach ($getServiceCollection as $key => $activeServices) {
                        # code...
                        if ($activeServices->getServiceType() == self::SERVICE_TYPE_FIXED) {
                            $productCount = count($checkSellerIdsInCart[$sellerId]);
                            $converedAmount = $this->convertCurrency(
                                $activeServices->getServiceValue(),
                                $currentCurrency,
                                $baseCurrency
                            );
                            $totalFees = $totalFees + (
                                $converedAmount / ($totalItemCount > 0 ?
                                    $totalItemCount : $productCount));
                            $title = $activeServices->getServiceTitle();
                            $value = $converedAmount;
                            array_push($cartServiceFeeArray, $title . " - " . $symbol . $value);
                        } else {
                            $totalFees = $totalFees + ($rowTotal * ($activeServices->getServiceValue() / 100));
                            $title = $activeServices->getServiceTitle();
                            $value = $activeServices->getServiceValue();
                            array_push($cartServiceFeeArray, $title . " - " . $value . "%");
                        }
                        $sellerserviceFee = implode(",", $cartServiceFeeArray);
                    }
                }
                $serviceTitle = "Service Fees";
                if ($sellerserviceFee != null && $sellerserviceFee != "") {
                    $serviceTitle = "Service Fees (" . $sellerserviceFee . ")";
                }
                
                $item->setServiceTitleList($sellerserviceFee);
                $item->setServiceTitle($serviceTitle);
                $item->setServiceFees($totalFees - $checktotalFees);
                $item->setCurrentCurrencyServiceFees($this->getFinalTotal($totalFees - $checktotalFees));
                $item->save();
            }
            $totalFees = $this->getFinalTotal($totalFees);
            return $totalFees;
        } catch (\Exception $e) {
            $this->_messageManager->addError("Something while calculating the total applicable fees went wrong.");
            $this->logger->error("Error in line 258 : " . $e->getMessage());
        }
    }

    /**
     * Get current currency symbol
     *
     * @return string
     */
    public function getCurrentCurrencySymbol()
    {
        $currency = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        return $this->_currency->load($currency)->getCurrencySymbol();
    }

    /**
     * Get total fees according to current store currency
     *
     * @param float $totalFees
     * @return float
     */
    public function getFinalTotal(float $totalFees)
    {
        $currentCurrency = $this->getCurrentCurrencyCode();
        $baseCurrency = $this->getBaseCurrencyCode();
        return $this->getConvertedCurrency($currentCurrency, $baseCurrency, $totalFees);
    }

    /**
     * Convert currency amount
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param int $amount
     * @return int $currencyAmount
     */
    public function getConvertedCurrency($fromCurrency, $toCurrency, $amount)
    {
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }

        if ($baseCurrencyCode == $toCurrency) {
            $currencyAmount = $amount / $rates[$fromCurrency];
        } else {
            $amount = $amount / $rates[$fromCurrency];
            $currencyAmount = $this->convertCurrency($amount, $baseCurrencyCode, $toCurrency);
        }
        return $currencyAmount;
    }

    /**
     * Convert amount according to currenct currency
     *
     * @param int $amount
     * @param string $from
     * @param string $to
     * @return int $finalAmount
     */
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_priceCurrency
            ->convert($amount, $to, $from);

        return $finalAmount;
    }

    /**
     * Get currency rates
     *
     * @param string $currency
     * @param string $toCurrencies
     * @return array
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * Return currency currency code
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Get base currency code
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Get all allowed currency in system config
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    /**
     * GetSellerList is used to get the list of all the sellers
     *
     * @return mixed
     */
    public function getSellerList()
    {
        $sellerCollection = $this->getSellerCollection();
        $sellerList[] = ['value' => 0, 'label' => __('Admin')];
        $customerGridFlat = $this->getSellerCollection()->getTable('customer_grid_flat');
        $sellerCollection->getSelect()->join(
            $customerGridFlat . ' as cgf',
            'main_table.seller_id = cgf.entity_id',
            [
                'name' => 'name',
            ]
        )->where('main_table.store_id = 0 AND main_table.is_seller = 1');
        foreach ($sellerCollection as $item) {
            $sellerList[] = ['value' => $item->getSellerId(), 'label' => $item->getName()];
        }
        return $sellerList;
    }

    /**
     * Get Seller Collection
     *
     * @return \Webkul\Marketplace\Model\ResourceModel\Seller\Collection
     */
    public function getSellerCollection()
    {
        return $this->_sellerCollectionFactory->create();
    }

    /**
     * Get marketplace helper
     *
     * @return marketplaceHelper
     */
    public function getMarketplaceHelper()
    {
        return $this->mpHelper;
    }

    /**
     * CheckSplitCart used to get all seller ids of products added in cart
     *
     * @return array [seller products]
     */
    public function checkSellerProductsInCart()
    {
        try {
            $quoteId = $this->cart->getQuoteId();
            $quote = $this->quoteFactory->create()->load($quoteId);
            $sellerIds = [];
            $itemCount = 0;
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $sellerId = $this->getSellerId($item->getProductId());
                    $sellerIds[$sellerId][$item->getProductId()] = $item->getQty();
                }
            }
            return $sellerIds;
        } catch (\Exception $e) {
            $this->logDataInLogger(
                " Exception : " . $e->getMessage()
            );
        }
    }

    /**
     * GetSellerId used to get seller id by giving a product id
     *
     * @param int $productid [contains product id]
     *
     * @return int [returns seller id]
     */
    public function getSellerId($productid)
    {
        try {
            $sellerId = 0;
            $model = $this->mpModel->getCollection()
                ->addFieldToFilter(
                    'mageproduct_id',
                    $productid
                );
            if ($model->getSize()) {
                foreach ($model as $value) {
                    $sellerId = $value->getSellerId();
                }
            }

            return $sellerId;
        } catch (\Exception $e) {
            $this->logDataInLogger(
                "Helper_Data_getSellerId Exception : " . $e->getMessage()
            );
        }
    }

    /**
     * LogDataInLogger
     *
     * @param string $data
     * @return void
     */
    public function logDataInLogger($data)
    {
        $this->logger->info($data);
    }

    /**
     * Get seller service fees
     *
     * @param Int $quoteId
     * @return servicefees
     */
    public function getSellerServiceFeeFromQuote($quoteId)
    {
        $sellerIdInSession = $this->mpHelper->getCustomerId();
        try {
            $quote = $this->quoteFactory->create()->load($quoteId);
            $sellerIds = [];
            $currCurrencyServiceFeeTotal = 0;
            $baseCurrencyServiceFeeTotal = 0;
            foreach ($quote->getAllVisibleItems() as $item) {
                if (!$item->hasParentItemId()) {
                    $sellerId = $this->getSellerId($item->getProductId());
                    if ($sellerId == $sellerIdInSession) {
                        $currCurrencyServiceFeeTotal += $item->getCurrentCurrencyServiceFees();
                        $baseCurrencyServiceFeeTotal += $item->getServiceFees();
                        $sellerIds[$sellerId]["currCurrencyServiceFeeTotal"] = $currCurrencyServiceFeeTotal;
                        $sellerIds[$sellerId]["baseservicefees"] = $baseCurrencyServiceFeeTotal;
                    }
                }
            }
            $sellerServiceName = $this->activeServiceNames($sellerIdInSession);

            $sellerIds[$sellerIdInSession]["sellerServiceName"] = $sellerServiceName;
            return $sellerIds;
        } catch (\Exception $e) {
            $this->logDataInLogger(
                " Exception : " . $e->getMessage()
            );
        }
    }
    
    /**
     * Get Seller ID Per Product.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     */
    public function getSellerIdPerProduct($item)
    {
        $sellerId = $this->mpHelper->getSellerIdByProductId($item->getProductId());
        return $sellerId;
    }
}
