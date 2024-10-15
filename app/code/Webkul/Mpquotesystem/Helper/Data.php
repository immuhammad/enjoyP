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

namespace Webkul\Mpquotesystem\Helper;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductFactory;
use Magento\Customer\Model\Customer;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Model\ResourceModel\Product;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Module\ModuleList;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Serialize\SerializerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const BASE_QTY = 1;
    public const PATH_QUOTE_CONFIG = 'mpquotesystem/default_config';

    /**
     * @var Magento\Catalog\Model\ResourceModel\Eav\Attribute
     */
    protected $_attribute;

    /**
     * @var Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $_typeConfigurable;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     * @var Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * @var \Webkul\Mpquotesystem\Model\Quotes
     */
    protected $_mpquote;

    /**
     * @var Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var mpproductCollectionFacory
     */
    protected $_mpproductCollectionFacory;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetaData;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cartModel;

    /**
     * @var ModuleManager
     */
    private $moduleManager;

    /**
     * @var ModuleList
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    private $_localeCurrency;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProductHelper;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @param \Magento\Framework\App\Helper\Context                $context
     * @param Attribute                                            $attribute
     * @param Configurable                                         $typeConfigurable
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param ProductFactory                                       $productFactory
     * @param \Magento\Customer\Model\Session                      $customerSession
     * @param Customer                                             $customerModel
     * @param StoreManagerInterface                                $storeManager
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory            $mpquotes
     * @param \Magento\Directory\Model\Currency                    $currency
     * @param \Magento\Catalog\Block\Product\ListProduct           $listProduct
     * @param \Magento\Eav\Model\Config                            $eavConfig
     * @param ProductRepositoryInterface                           $productRepository
     * @param Product\CollectionFactory                            $productCollectionFactory
     * @param ProductMetadataInterface                             $productMetaData
     * @param \Magento\Framework\App\Request\Http                  $request
     * @param \Magento\Checkout\Model\Cart                         $cartModel
     * @param ModuleList                                           $moduleList
     * @param \Magento\Framework\Locale\CurrencyInterface          $localeCurrency
     * @param \Magento\Framework\Pricing\Helper\Data               $pricingHelper
     * @param \Magento\Catalog\Helper\Product                      $catalogProductHelper
     * @param \Magento\Directory\Helper\Data                       $directoryHelper
     * @param \Webkul\Mpquotesystem\Model\QuoteConfigFactory       $quoteconfigFactory
     * @param \Webkul\Marketplace\Helper\Data                      $mpHelper
     * @param \Magento\Framework\Filesystem                        $fileSystem
     * @param \Magento\Framework\Filesystem\Driver\File            $fileDriver
     * @param \Magento\Framework\Json\Helper\Data                  $jsonHelper
     * @param UploaderFactory                                      $fileUploaderFactory
     * @param SerializerInterface                                  $serializer
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Attribute $attribute,
        Configurable $typeConfigurable,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        ProductFactory $productFactory,
        \Magento\Customer\Model\Session $customerSession,
        Customer $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Eav\Model\Config $eavConfig,
        ProductRepositoryInterface $productRepository,
        Product\CollectionFactory $productCollectionFactory,
        ProductMetadataInterface $productMetaData,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Checkout\Model\Cart $cartModel,
        ModuleList $moduleList,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Directory\Helper\Data $directoryHelper,
        \Webkul\Mpquotesystem\Model\QuoteConfigFactory $quoteconfigFactory,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\Filesystem $fileSystem,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        UploaderFactory $fileUploaderFactory,
        SerializerInterface $serializer
    ) {
        parent::__construct($context);
        $this->_directoryHelper = $directoryHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_attribute = $attribute;
        $this->_listProduct = $listProduct;
        $this->_typeConfigurable = $typeConfigurable;
        $this->_stockRegistry = $stockRegistry;
        $this->_productFactory = $productFactory;
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_storeManager = $storeManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_mpquote = $mpquotes;
        $this->_currency = $currency;
        $this->_eavConfig = $eavConfig;
        $this->_productRepository = $productRepository;
        $this->_mpproductCollectionFacory = $productCollectionFactory;
        $this->productMetaData = $productMetaData;
        $this->_request = $request;
        $this->cartModel = $cartModel;
        $this->moduleManager = $context->getModuleManager();
        $this->moduleList = $moduleList;
        $this->_localeCurrency = $localeCurrency;
        $this->catalogProductHelper = $catalogProductHelper;
        $this->quoteconfigFactory = $quoteconfigFactory;
        $this->mpHelper = $mpHelper;
        $this->fileSystem = $fileSystem;
        $this->fileDriver = $fileDriver;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->serializer = $serializer;
        $this->jsonHelper = $jsonHelper;
    }
    
    /**
     * Use to get price and options of a product which are used when quote is submitted.
     *
     * @param Magento\catalog\Model\Product $product
     * @param Webkul\Mpquotesystem\Model\Quotes $quote
     *
     * @return string
     */
    public function getOptionNPrice($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        if ($product->getTypeId() == 'bundle') {
            list($returnOptionString, $returnFinalPrice) = $this->getBundleProductData($product, $quote);
            $optionString .= $returnOptionString;
            $finalPrice += $returnFinalPrice;
        } else {
            $finalPrice = $quote->getProductPrice();
            if ($product->getTypeId() == 'configurable') {
                list($returnOptionString, $returnFinalPrice) = $this->getConfigurableProductData(
                    $product,
                    $quote
                );
                $optionString .= $returnOptionString;
                $finalPrice += $returnFinalPrice;
            }

            list($returnOptionString, $returnFinalPrice) = $this->getCustomOptionData($product, $quote);
            $optionString .= $returnOptionString;
            $finalPrice += $returnFinalPrice;
            $links = $this->convertStringAccToVersion($quote->getLinks(), 'decode');
            if (isset($links[0]) && $links !="null" && is_array($links)) {
                $optionString .= "<b><dt>".__('Links').'</dt></b>';
                foreach ($links as $link) {
                    $productlinks = $product->getTypeInstance()->getLinks($product);
                    if (is_array($productlinks)) {
                        foreach ($productlinks as $productlink) {
                            $optionString = $this->getOptionString($productlink, $optionString, $link);
                        }
                    }
                }
            }
        }
        return $optionString.'~|~'.$finalPrice;
    }

    /**
     * Get Option String
     *
     * @param object $productlink
     * @param string $optionString
     * @param string $link
     *
     * @return void
     */
    public function getOptionString($productlink, $optionString, $link)
    {
        if ($productlink->getLinkId() == $link) {
            $optionString .= "<dd>".$productlink->getTitle().'</dd><br>';
        }
        return $optionString;
    }

    /**
     * [getBundleProductData get Option name and price of bundle product]
     *
     * @param Catalog/Model/Product      $product
     * @param MpQuotesystem/Model/Quotes $quote
     * @return string
     */
    public function getBundleProductData($product, $quote)
    {
        $finalPrice = 0;
        $optionString = '';
        $bundleOptions = $this->convertStringAccToVersion($quote->getBundleOption(), 'decode');
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                $optionString .= "<b><dt>"." ".
                    $options->getDefaultTitle().
                    '</dt></b>';
                $optArray = [];
                foreach ($selectionCollection as $proselection) {
                    if (is_array($valueId)) {
                        $finalPrice = $this->updateFinalPrice($finalPrice, $proselection, $valueId);
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $bundleOptionData = $this->getUpdatedBundleOptionData(
                                $optionId,
                                $bundleOptions,
                                $proselection
                            );
                            
                            $optArray[] = $bundleOptionData.
                                ' x '.
                                $proselection->getName().
                                ' '.
                                '</dd><dd>'.
                                $this->getformattedPrice(
                                    $proselection->getPrice(),
                                    true,
                                    false
                                ).'</dd>';
                            $finalPrice += (
                                $bundleOptionData * $proselection->getPrice()
                            );
                        }
                    }
                }
                $optionString .= "<dd>"." ".
                    implode('<br>', $optArray).'</dd></br>';
            }
        }
        return [$optionString, $finalPrice];
    }

    /**
     * Get Updated Bundle Option Data
     *
     * @param int    $optionId
     * @param array  $bundleOptions
     * @param object $proselection
     *
     * @return void
     */
    public function getUpdatedBundleOptionData($optionId, $bundleOptions, $proselection)
    {
        if (array_key_exists($optionId, $bundleOptions['bundle_option_qty'])) {
            $bundleOptionData = $bundleOptions['bundle_option_qty'][$optionId];
        } else {
            $bundleOptionData = $proselection->getSelectionQty();
        }
        return $bundleOptionData;
    }

    /**
     * Get customer session
     *
     * @return object
     */
    public function getCustomerSession()
    {
        return $this->_customerSession->getCustomer();
    }

    /**
     * Get formatted Price
     *
     * @param float   $price
     * @param boolean $flag
     *
     * @return string
     */
    public function getformattedPrice($price, $flag = true)
    {
        return $this->_pricingHelper
            ->currency($price, $flag, false);
    }

    /**
     * Use to get quote quantity is valid or not according to product's quantity.
     *
     * @param Magento\catalog\Model\Product $product
     * @param Webkul\Mpquotesystem\Model\Quotes $quote
     * @param int $qty
     *
     * @return int
     */
    public function validateQuantity($product, $quote, $qty)
    {
        $validQty = 1;
        if (in_array($product->getTypeId(), ['simple', 'downloadable', 'virtual'])) {
            $productQty = $this->_stockRegistry->getStockItem(
                $product->getId(),
                $product->getStore()->getWebsiteId()
            )->getQty();
            if ($productQty < $qty) {
                $validQty = 0;
            }
        }
        if ($product->getTypeId() == 'configurable') {
            $validQty = $this->validateQtyForConfigurableProduct($quote, $product, $qty, $validQty);
        } elseif ($product->getTypeId() == 'bundle') {
            $validQty = $this->validateQtyForBundleProduct($quote, $product, $qty);
        }
        return $validQty;
    }

    /**
     * Validate Qty For Bundle Product
     *
     * @param object $quote
     * @param object $product
     * @param int    $qty
     *
     * @return bool
     */
    public function validateQtyForBundleProduct($quote, $product, $qty)
    {
        $validQty = 1;
        $bundleOptions = $this->convertStringAccToVersion($quote->getBundleOption(), 'decode');
        $typeInstance = $product->getTypeInstance(true)->setStoreFilter($product->getStoreId(), $product);
        $selectionCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($product),
            $product
        )->getData();
        foreach ($selectionCollection as $selection) {
            foreach ($bundleOptions['bundle_option'] as $key => $bundleOption) {
                if ($selection['option_id'] == $key) {
                    if (is_array($bundleOption)) {
                        foreach ($bundleOption as $bundleSubOption) {
                            $validQty = $this->checkValidQty($bundleSubOption, $selection, $validQty);
                        }
                    } else {
                        if ($bundleOption == $selection['selection_id']) {
                            $selectionProduct = $this->_productRepository
                                ->getById($selection['product_id']);
                            $productQty = $this->_stockRegistry
                                ->getStockItem(
                                    $selectionProduct->getId(),
                                    $selectionProduct->getStore()
                                        ->getWebsiteId()
                                )->getQty();
                            $validQty = $this->validateBundleQty($productQty, $bundleOptions, $qty, $validQty);
                        }
                    }
                }
            }
        }
        return $validQty;
    }

    /**
     * Validate Bundle Qty
     *
     * @param int   $productQty
     * @param array $bundleOptions
     * @param int   $qty
     * @param bool  $validQty
     *
     * @return int
     */
    public function validateBundleQty($productQty, $bundleOptions, $qty, $validQty)
    {
        if ($productQty <= $bundleOptions['bundle_option_qty'][$key] * $qty) {
            $validQty = 0;
        }
        return $validQty;
    }

    /**
     * Check Valid Qty
     *
     * @param array $bundleSubOption
     * @param array $selection
     * @param int   $validQty
     *
     * @return int
     */
    public function checkValidQty($bundleSubOption, $selection, $validQty)
    {
        if ($bundleSubOption == $selection['selection_id']) {
            $selectionProduct = $this->_productRepository
                ->getById($selection['product_id']);
            $productQty = $this->_stockRegistry
                ->getStockItem(
                    $selectionProduct->getId(),
                    $selectionProduct->getStore()->getWebsiteId()
                )->getQty();
            if ($productQty < $selection['selection_qty'] * $qty) {
                $validQty = 0;
            }
        }
        return $validQty;
    }

    /**
     * Validate Qty For Configurable Product
     *
     * @param object $quote
     * @param object $product
     * @param int    $qty
     * @param int    $validQty
     *
     * @return int
     */
    public function validateQtyForConfigurableProduct($quote, $product, $qty, $validQty)
    {
        $validQty = 1;
        $configurableOptions = $this->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');
        
        $childProducts = $this->_typeConfigurable->getUsedProducts($product);
        foreach ($childProducts as $child) {
            foreach ($configurableOptions as $key => $configurableOption) {
                $configattr = $child->getData($this->loadData($this->_attribute, $key)->getAttributeCode());
                if ($configattr == $configurableOption) {
                    $childProduct = $this->_productRepository
                        ->getById($child->getId());
                    $productQty = $this->_stockRegistry->getStockItem(
                        $childProduct->getId(),
                        $childProduct->getStore()->getWebsiteId()
                    )->getQty();

                    if ($productQty < $qty) {
                        $validQty = 0;
                    }
                }
            }
        }
        return $validQty;
    }

    /**
     * Get email id from admin system config
     *
     * @return string
     */
    public function getDefaultTransEmailId()
    {
        return $this->scopeConfig->getValue(
            'trans_email/ident_general/email',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Function to get Price display value from config
     *
     * @return boolean
     */
    public function getConfigShowPrice()
    {
        if ($this->getQuoteEnabled()) {
            return $this->scopeConfig->getValue(
                'mpquotesystem/quotesystem_settings/allowed_showprice',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Get customer data by customer id
     *
     * @param int $customerId
     *
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->_customerModel->load($customerId);
    }

    /**
     * GetProduct
     *
     * @param int $productId
     * @return object
     */
    public function getProduct($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

    /**
     * GetMediaUrl
     *
     * @return string
     */
    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()
            ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    /**
     * GetStore
     *
     * @return object
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * GetWkQuoteModel
     *
     * @return object
     */
    public function getWkQuoteModel()
    {
        return $this->_mpquote->create();
    }

    /**
     * GetCheckoutSession
     *
     * @return object
     */
    public function getCheckoutSession()
    {
        return $this->_checkoutSession;
    }
    
    /**
     * GetCurrentCurrencyCode
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * GetBaseCurrencyCode
     *
     * @return string
     */
    public function getBaseCurrencyCode()
    {
        return $this->_storeManager->getStore()->getBaseCurrencyCode();
    }

    /**
     * Retrieve currency Symbol.
     *
     * @return string
     */
    public function getBaseCurrencySymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getBaseCurrencyCode()
        )->getSymbol();
    }

    /**
     * GetConfigAllowCurrencies
     *
     * @return array
     */
    public function getConfigAllowCurrencies()
    {
        return $this->_currency->getConfigAllowCurrencies();
    }

    /**
     * GetCurrencyRates
     *
     * @param string $currency
     * @param string $toCurrencies
     * @return object
     */
    public function getCurrencyRates($currency, $toCurrencies = null)
    {
        return $this->_currency->getCurrencyRates($currency, $toCurrencies); // give the currency rate
    }

    /**
     * ConvertCurrency
     *
     * @param double $amount
     * @param string $from
     * @param string $to
     * @return string
     */
    public function convertCurrency($amount, $from, $to)
    {
        $finalAmount = $this->_directoryHelper
            ->currencyConvert($amount, $from, $to);

        return $finalAmount;
    }
    
    /**
     * GetwkconvertCurrency
     *
     * @param string $fromCurrency
     * @param string $toCurrency
     * @param double $amount
     * @param string $quoteCurrencyCode
     * @return string
     */
    public function getwkconvertCurrency($fromCurrency, $toCurrency, $amount, $quoteCurrencyCode = null)
    {
        $currentCurrencyCode = $this->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$fromCurrency])) {
            $rates[$fromCurrency] = 1;
        }
        if ($quoteCurrencyCode) {
            $quoteCurrencyRate = $this->getCurrencyRate($quoteCurrencyCode);
            $currentCurrencyRate = $this->getCurrencyRate($currentCurrencyCode);
            $baseCurrencyAmount = $amount/$quoteCurrencyRate;
            $currencyAmount = $baseCurrencyAmount * $currentCurrencyRate;
            return $currencyAmount;
        }
        return $amount;
    }

    /**
     * Get currency rate
     *
     * @param string $currency
     * @return void | int
     */
    public function getCurrencyRate($currency)
    {
        return $this->_storeManager->getStore()
            ->getBaseCurrency()->getRate($currency);
    }

    /**
     * Get config settings for redirect url after adding product to cart
     *
     * @return string
     */
    public function getRedirectConfigSetting()
    {
        return $this->scopeConfig->getValue(
            'checkout/cart/redirect_to_cart',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Quoted Product Info
     *
     * @param object $_productCollection
     *
     * @return void
     */
    public function getQuotedProductInfo($_productCollection)
    {
        $auctionModuleEnabledOrNot = $this->checkModuleIsEnabledOrNot('Webkul_MpAuction');
        $quoteProductsInfo = [];
        $isConfigureable = 0;
        foreach ($_productCollection as $product) {
            if ($product->getTypeId() == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
                $isConfigureable = 1;
            }
            $productData = $this->getProduct($product->getId());
            $data = $this->_listProduct->getAddToCartPostParams($product);
            
            if ($productData->getQuoteStatus() === '1' || $globalData = $this->checkProductHasQuote($productData)) {
                if ($productData->getQuoteStatus() !== '1') {
                    $minqty = $globalData['min_qty'];
                } else {
                    $minqty = $productData->getMinQuoteQty();
                }
                $auctionCheck = 1;
                if ($auctionModuleEnabledOrNot) {
                    $auctionValues = $productData->getAuctionType();
                    $auctionOpt = explode(',', $auctionValues);
                    if (in_array(2, $auctionOpt)) {
                        $auctionCheck = 0;
                    }
                }
                if ($auctionCheck) {
                    $productUrl = $data['action'];
                    if (!$productData->getTypeInstance()->isPossibleBuyFromList($productData)) {
                        $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                        $quoteProductsInfo[$productData->getId()]['status'] = 0;
                        $quoteProductsInfo[$productData->getId()]['is_configureable'] = $isConfigureable;
                    } else {
                        if ($minqty=='' || $minqty==null) {
                            $minqty = 0;
                        }
                        $quoteProductsInfo[$productData->getId()]['min_qty'] = $minqty;
                        $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                        $quoteProductsInfo[$productData->getId()]['status'] = 1;
                        $quoteProductsInfo[$productData->getId()]['is_configureable'] = $isConfigureable;
                    }
                }
            }
        }
        return $quoteProductsInfo;
    }
    
    /**
     * GetQuoteEnabled
     *
     * @return bool
     */
    public function getQuoteEnabled()
    {
        return $this->scopeConfig->getValue(
            'mpquotesystem/quotesystem_settings/enable_quote',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * GetSellerIdByProductId
     *
     * @param int $productId
     * @return int
     */
    public function getSellerIdByProductId($productId)
    {
        $productCollection = $this->_mpproductCollectionFacory->create()
            ->addFieldToFilter('mageproduct_id', $productId);
        if ($productCollection->getSize()) {
            foreach ($productCollection as $product) {
                return $product->getSellerId();
            }
        }
        return 0;
    }
    
    /**
     * FinalPriceByProOption
     *
     * @param object $proOption
     * @param array  $option
     * @return int
     */
    protected function finalPriceByProOption($proOption, $option)
    {
        $finalPrice = 0;
        if (in_array($proOption->getType(), ['area', 'field'])) {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif (in_array($proOption->getType(), ['drop_down', 'radio'])) {
            $optionValues = $proOption->getValues();
            foreach ($optionValues as $optVal) {
                if ($option == $optVal->getOptionTypeId()) {
                    $finalPrice += $optVal->getPrice();
                    return $finalPrice;
                }
            }
        } elseif (in_array($proOption->getType(), ['multiple', 'checkbox'])) {
            $optionValues = $proOption->getValues();
            foreach ($optionValues as $optVal) {
                if (in_array($optVal->getOptionTypeId(), $option)) {
                    $finalPrice += $optVal->getPrice();
                    return $finalPrice;
                }
            }
        } else {
            $finalPrice = $this->finalPriceByDateTime($proOption, $finalPrice);
            return $finalPrice;
        }
    }
    
    /**
     * FinalPriceByDateTime
     *
     * @param object $proOption
     * @param int $finalPrice
     *
     * @return int
     */
    protected function finalPriceByDateTime($proOption, $finalPrice)
    {
        if ($proOption->getType() == 'date_time') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif ($proOption->getType() == 'date') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        } elseif ($proOption->getType() == 'time') {
            $finalPrice += $proOption->getPrice();
            return $finalPrice;
        }
    }

    /**
     * CalculateMinimumPriceOfProduct
     *
     * @param object $product
     * @param array  $params
     * @return int
     */
    public function calculateMinimumPriceOfProduct($product, $params)
    {
        if (isset($params['selected_configurable_option']) && $params['selected_configurable_option']=="") {
            $tierPrice = $this->getTierPrice($params, $product);
            $regularPrice = $product->getPrice($params, $product);
            $specialPrice = $this->getSpecialPrice($product);
            $mainPrice = $this->getMinPriceOfProduct($tierPrice, $regularPrice, $specialPrice);
            if (!isset($params['options'])) {
                return $mainPrice;
            } else {
                return $this->customPrice($params, $product, $mainPrice);
            }
        } else {
            return $product->getPrice();
        }
    }
    
    /**
     * Get Tier Price if tierPrice is set else return 0
     *
     * @param array $params
     * @param object $product
     *
     * @return decimal
     */
    public function getTierPrice($params, $product)
    {
        $tierPrice = $product->getTierPrice($params['quote_qty']);
        if ($tierPrice != '') {
            return $tierPrice;
        } else {
            return 0;
        }
    }
    
    /**
     * Return specialPrice if specialPrice is set else return 0
     *
     * @param object $product
     *
     * @return price
     */
    public function getSpecialPrice($product)
    {
        if ($product->getSpecialPrice() != "") {
            return $product->getSpecialPrice();
        } else {
            return 0;
        }
    }
    
    /**
     * Get the minimum price among $tier, $regular, $special
     *
     * @param int $tier
     * @param int $regular
     * @param int $special
     *
     * @return int
     */
    public function getMinPriceOfProduct($tier, $regular, $special)
    {
        if ($tier!=0 && $regular!=0 && $special!=0) {
            $minPrice = ($tier < $special) ?
            ($tier < $regular? $tier:$regular) : ($special < $regular? $special:$regular) ;
        } elseif ($tier!=0) {
            $minPrice = $tier<$regular ? $tier : $regular;
        } elseif ($special!=0) {
            $minPrice = $special<$regular ? $special : $regular;
        } else {
            $minPrice = $regular;
        }
        return $minPrice;
    }
    
    /**
     * Get price of product if custom Option is selected
     *
     * @param array $params
     * @param object $product
     * @param string $mainPrice
     *
     * @return finalPrice
     */
    public function customPrice($params, $product, $mainPrice)
    {
        $customOptions = $params['options'];
        $customOptionPrice = 0;
        $customOptionTotalPrice = 0;
        $productOptions = $product->getOptions();
        foreach ($customOptions as $key => $customOption) {
            $customOptionPrice = 0;
            $optionType = '';
            foreach ($productOptions as $proOption) {
                if ($proOption->getOptionId() == $key) {
                    if ($proOption->getValues()) {
                        list($customOptionPrice, $optionType) = $this->getCustomOptionPriceFromValues(
                            $proOption,
                            $customOption
                        );
                    } else {
                        if ($customOption!='') {
                            $customOptionPrice = $proOption->getPrice();
                            $optionType = $proOption->getDefaultPriceType();
                        }
                    }
                    $customOptionTotalPrice = $this->calculateCustomPriceAccToPriceType(
                        $optionType,
                        $customOptionPrice,
                        $mainPrice,
                        $customOptionTotalPrice
                    );
                }
            }
        }
        $finalPrice = $mainPrice + $customOptionTotalPrice;
        return $finalPrice;
    }

    /**
     * GetCustomOptionPriceFromValues
     *
     * @param object $proOption
     * @param array  $customOption
     * @return array
     */
    public function getCustomOptionPriceFromValues($proOption, $customOption)
    {
        foreach ($proOption->getValues() as $optionValue) {
            if ($customOption == $optionValue->getOptionTypeId()) {
                $customOptionPrice = $optionValue->getPrice();
                $optionType = $optionValue->getDefaultPriceType();
            }
        }
        return [$customOptionPrice, $optionType];
    }

    /**
     * CalculateCustomPriceAccToPriceType
     *
     * @param string $optionType
     * @param int    $customOptionPrice
     * @param int    $mainPrice
     * @param int    $customOptionTotalPrice
     * @return int
     */
    public function calculateCustomPriceAccToPriceType(
        $optionType,
        $customOptionPrice,
        $mainPrice,
        $customOptionTotalPrice
    ) {
        if ($optionType=='percent') {
            $customOptionTotalPrice += ($mainPrice * $customOptionPrice)/100;
        } else {
            $customOptionTotalPrice += $customOptionPrice;
        }
        return $customOptionTotalPrice;
    }
    
    /**
     * ConvertStringAccToVersion
     *
     * @param string $string
     * @param string $type
     * @return string
     */
    public function convertStringAccToVersion($string, $type)
    {
        if ($string!='') {
            $moduleData = $this->moduleList->getOne('Webkul_Mpquotesystem');
            $moduleVersion = $moduleData['setup_version'];
            $magentoVersion = $this->productMetaData->getVersion();
            if ($moduleVersion == null || version_compare($moduleVersion, '2.0.3')>=0) {
                if ($type=='encode') {
                    return $this->getJsonObject()->jsonEncode($string);
                } else {
                    $object = $this->getJsonObject()->jsonDecode($string);
                    if (is_object($object)) {
                        return $this->getJsonObject()->jsonDecode($this->getJsonObject()->jsonEncode($object), true);
                    }
                    return $object;
                }
            } else {
                if ($type=='encode') {
                    return $this->serializer->serialize($string);
                } else {
                    return $this->serializer->unserialize($string);
                }
            }
        }
        return $string;
    }

    /**
     * GetQuoteAttachmentsArr
     *
     * @param string|null $attachments
     * @return array
     */
    public function getQuoteAttachmentsArr($attachments)
    {
        $attachmentsArr = [];
        if (!empty($attachments)) {
            $attachmentsArrData = explode(',', $attachments);
            foreach ($attachmentsArrData as $attachment) {
                $attachmentArr = explode('/', $attachment);
                $index = count($attachmentArr) - 1;
                if (isset($attachmentArr[$index])) {
                    $attachmentsArr[$attachment] = $attachmentArr[$index];
                } else {
                    $attachmentsArr[$attachment] = $attachment;
                }
            }
        }
        return $attachmentsArr;
    }

    /**
     * Return module status
     *
     * @return boolean
     */
    public function getModuleStatus()
    {
        return $this->scopeConfig->getValue(
            'mpquotesystem/quotesystem_settings/enable_quote',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    
    /**
     * Function to get Minimum Config Quote Qty
     *
     * @return int
     */
    public function getConfigMinQty() : int
    {
        return (int)$this->scopeConfig->getValue(
            'mpquotesystem/quotesystem_settings/min_quote_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }
    
    /**
     * GetCurrentUrl
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl();
    }
    
    /**
     * IsQuoteItem
     *
     * @param array $item
     * @return int
     */
    public function isQuoteItem($item)
    {
        $quoteId = 0;
        $params = $this->_request->getParams();
        if (is_array($params) && array_key_exists('quote_id', $params) && $params['quote_id']>0) {
            $quoteId = $params['quote_id'];
        }
        return $quoteId;
    }
    
    /**
     * GetCart
     *
     * @return object
     */
    public function getCart()
    {
        return $this->cartModel;
    }
    
    /**
     * CheckQuoteProductinItem
     *
     * @param object $item
     * @return bool
     */
    public function checkQuoteProductinItem($item)
    {
        if ($item->getItemId()) {
            $quoteCollection = $this->getWkQuoteModel()->getCollection();
            $quoteCollection->addFieldToFilter('item_id', $item->getItemId());
            if ($quoteCollection->getSize()) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * CheckModuleIsEnabledOrNot
     *
     * @param string $moduleName
     * @return boolean
     */
    public function checkModuleIsEnabledOrNot($moduleName)
    {
        if ($this->moduleManager->isEnabled($moduleName)) {
            if ($this->moduleManager->isOutputEnabled($moduleName)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Function to get Product from repository
     *
     * @param [int] $id
     */
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }

    /**
     * Function to remove price info from html
     *
     * @param  [string] $html
     * @return string
     */
    public function removePriceInfo($html) : string
    {
        $newDom = new \DOMDocument();
        libxml_use_internal_errors(true);
        $newDom->loadHTML($html);
        $spanTags = $newDom->getElementsByTagName('span');
        for ($i = $spanTags->length; --$i >= 0;) {
            $span = $spanTags->item($i);
            $span->parentNode->removeChild($span);
        }
        return $newDom->saveHTML();
    }
    
    /**
     * GetBundleProductQuatity
     *
     * @param object $product
     * @param array  $bundleOptions
     * @return int
     */
    public function getBundleProductQuatity($product, $bundleOptions)
    {
        $quantity = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    $selectedProductId = 0;
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    }
                    if ($selectedProductId) {
                        $selectedProduct = $this->getProduct($selectedProductId);
                        $quantity+=$selectedProduct->getQuantityAndStockStatus()['qty'];
                    }
                }
            }
        }
        return $quantity;
    }
    
    /**
     * CalculateProductPrice
     *
     * @param array $params
     * @return int
     */
    public function calculateProductPrice($params)
    {
        $finalPrice = 0;
        $productId = $params['product'];
        $product = $this->getProduct($productId);
        
        if (in_array($product->getTypeId(), ['simple', 'downloadable', 'virtual'])) {
            $finalPrice += $product->getPriceModel()->getFinalPrice(self::BASE_QTY, $product);
        } elseif ($product->getTypeId() == 'bundle') {
            $finalPrice += $this->getBundleProductPrice(
                $product,
                $params['bundle_option_to_calculate']
            );
        } elseif ($product->getTypeId() == 'configurable') {
            $finalPrice = $this->getConfigurableProductPrice($product, $params);
        }
        if (array_key_exists('links', $params) && !empty($params['links'])) {
            $finalPrice += $this->getProductPriceByLinks($product, $params['links'], $finalPrice);
        }
        if (array_key_exists('options', $params) && !empty($params['options'])) {
            $finalPrice += $this->getProductOptionsPrice($product, $params['options'], $finalPrice);
        }
        return $finalPrice;
    }
    
    /**
     * GetBundleProductPrice
     *
     * @param object $product
     * @param array  $bundleOptions
     * @return int
     */
    public function getBundleProductPrice($product, $bundleOptions)
    {
        $finalPrice = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectionQty = $product->getCustomOption(
                                'selection_qty_' .
                                $proselection->getSelectionId()
                            );
                            $finalPrice += $product->getPriceModel()
                                ->getSelectionFinalTotalPrice(
                                    $product,
                                    $proselection,
                                    self::BASE_QTY,
                                    $selectionQty,
                                    $multiplyQty = true,
                                    $takeTierPrice = true
                                );
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectionQty = $product->getCustomOption(
                                'selection_qty_' .
                                $proselection->getSelectionId()
                            );
                            $finalPrice += $product->getPriceModel()->getSelectionFinalTotalPrice(
                                $product,
                                $proselection,
                                self::BASE_QTY,
                                $selectionQty,
                                true,
                                false
                            );
                        }
                    }
                }
            }
        }
        return $finalPrice;
    }
    
    /**
     * GetConfigurableProductPrice
     *
     * @param object $product
     * @param array  $params
     * @return int
     */
    public function getConfigurableProductPrice($product, $params)
    {
        $finalPrice = 0;
        $product = $this->getProduct($params['product']);
        $childProductId = $this->_typeConfigurable
            ->getProductByAttributes(
                $params['super_attribute'],
                $product
            )->getId();
        $childProductProduct = $this->getProduct($childProductId);
        $finalPrice += $childProductProduct->getPriceModel()->getFinalPrice(self::BASE_QTY, $childProductProduct);
        return $finalPrice;
    }
    
    /**
     * GetProductPriceByLinks
     *
     * @param object $product
     * @param array  $links
     * @param int    $basePrice
     * @return int
     */
    public function getProductPriceByLinks($product, $links, $basePrice)
    {
        $finalPrice = 0;
        $productLinks = $product->getTypeInstance()->getLinks($product);
        foreach ($links as $linkKey => $linkId) {
            if (isset($productLinks[$linkId])) {
                $finalPrice += $productLinks[$linkId]->getPrice();
            }
        }
        return $finalPrice;
    }
    
    /**
     * GetProductOptionsPrice
     *
     * @param object $product
     * @param object $productOptions
     * @param int    $basePrice
     * @return int
     */
    public function getProductOptionsPrice($product, $productOptions, $basePrice)
    {
        $options = $productOptions;
        $productOptions = $product->getOptions();
        if (is_array($options)) {
            $finalPrice = $this->getFinalPrice($options, $productOptions, $basePrice);
        }
        return $finalPrice;
    }
    
    /**
     * Get Final Price
     *
     * @param array $options
     * @param array $productOptions
     * @param string $basePrice
     *
     * @return array
     */
    public function getFinalPrice($options, $productOptions, $basePrice)
    {
        $finalPrice = 0;
        foreach ($options as $key => $option) {
            if (!empty($option)) {
                foreach ($productOptions as $proOption) {
                    if ($proOption->getOptionId() == $key) {
                        $group = $proOption->groupFactory($proOption->getType())
                            ->setOption($proOption)
                            ->setConfigurationItemOption($proOption);
                        if (is_array($option)) {
                            $option = implode(',', $option);
                        }
                        $finalPrice += $group->getOptionPrice($option, $basePrice);
                    }
                }
            }
        }

        return $finalPrice;
    }

    /**
     * GetCustomOptionData
     *
     * @param object $product
     * @param object $quote
     * @return array
     */
    public function getCustomOptionData($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        if (!$product->getSku()) {
            return [$optionString, $finalPrice];
        }
        $options = $this->convertStringAccToVersion($quote->getProductOption(), 'decode');
        $productOptions = $product->getOptions();
        if (is_array($options)) {
            foreach ($options as $key => $option) {
                foreach ($productOptions as $proOption) {
                    if ($proOption->getOptionId() == $key) {
                        list(
                            $returnOptionString,
                            $returnFinalPrice
                        ) = $this->getOptionStringOfOptions($proOption, $option);
                        $optionString .= $returnOptionString;
                        $finalPrice += $returnFinalPrice;
                    }
                }
            }
        }
        return [$optionString, $finalPrice];
    }
    
    /**
     * GetOptionStringOfOptions
     *
     * @param array $proOption
     * @param array $option
     * @return array
     */
    public function getOptionStringOfOptions($proOption, $option)
    {
        $finalPrice = 0;
        $optionString = '';
        $optionString .= "<b><dt>".
            $proOption['default_title'].
            '</dt></b> :';
        if (in_array($proOption->getType(), ['area', 'field'])) {
            $finalPrice += $proOption->getPrice();
            $optionString .= "<dd>".
                $option.
                '</dd>';
        } elseif (in_array($proOption->getType(), ['drop_down', 'radio'])) {
            list($optionStringUpdated, $finalPrice) = $this->byDropDownAndRadio($proOption, $option);
            $optionString .= $optionStringUpdated;
        } elseif (in_array($proOption->getType(), ['multiple', 'checkbox'])) {
            $optionValues = $proOption->getValues();
            $displayableOptions = [];
            list($finalPrice, $displayableOptions) = $this->byMultipleAndcheckbox($proOption, $option);
            $optionString .= "<dd>".
                implode(', ', $displayableOptions).
                '</dd>';
        } elseif ($proOption->getType() == 'date_time') {
            $finalPrice += $proOption->getPrice();
            $dateTime = $option['month'].
                '/'.$option['day'].
                '/'.$option['year'].
                ' '.date(
                    'H:i',
                    strtotime(
                        $option['hour'].':'.$option['minute']
                    )
                ).' '.
                strtoupper($option['day_part']);
            $optionString .= "<dd>".$dateTime.'</dd>';
        } elseif ($proOption->getType() == 'date') {
            $finalPrice += $proOption->getPrice();
            $dateTime = $option['month'].
                '/'.$option['day'].
                '/'.$option['year'];
            $optionString .= "<dd>".$dateTime.'</dd>';
        } elseif ($proOption->getType() == 'time') {
            $finalPrice += $proOption->getPrice();
            $dateTime = date(
                'H:i',
                strtotime($option['hour'].':'.$option['minute'])
            ).' '.strtoupper($option['day_part']);
            $optionString .= "<dd>".$dateTime.'</dd>';
        }
        return [$optionString,$finalPrice];
    }
    
    /**
     * ByDropDownAndRadio
     *
     * @param object $proOption
     * @param object $option
     * @return array
     */
    protected function byDropDownAndRadio($proOption, $option)
    {
        $finalPrice = 0;
        $optionString = '';
        $optionValues = $proOption->getValues();
        foreach ($optionValues as $optVal) {
            if ($option == $optVal->getOptionTypeId()) {
                $finalPrice += $optVal->getPrice();
                $optionString .= "<dd>".
                    $optVal->getDefaultTitle().
                    '</dd><br>';
            }
        }
        return [$optionString,$finalPrice];
    }

    /**
     * ByMultipleAndcheckbox
     *
     * @param object $proOption
     * @param array  $option
     * @return array
     */
    protected function byMultipleAndcheckbox($proOption, $option)
    {
        $finalPrice = 0;
        $displayableOptions = [];
        foreach ($proOption as $optVal) {
            if (in_array($optVal->getOptionTypeId(), $option)) {
                $finalPrice += $optVal->getPrice();
                $displayableOptions[] = $optVal->getDefaultTitle();
            }
        }
        return [$finalPrice, $displayableOptions];
    }
    
    /**
     * GetConfigurableProductData
     *
     * @param object $product
     * @param object $quote
     * @return array
     */
    public function getConfigurableProductData($product, $quote)
    {
        $optionString = '';
        $finalPrice = 0;
        $configurableOptions = $this->convertStringAccToVersion($quote->getSuperAttribute(), 'decode');

        $attributes = $product->getTypeInstance(true)->getConfigurableAttributes($product);

        foreach ($attributes as $attribute) {
            $attributeModel = $this->_eavConfig->getAttribute(
                \Magento\Catalog\Model\Product::ENTITY,
                $attribute['attribute_id']
            );
            $attributeCode = $attributeModel->getAttributeCode();
            $attributeArray[$attribute['attribute_id']] = $attributeCode;
        }
        $productPrice = 0;
        $productIdArray = [];
        $productWithAttributes = $product->getTypeInstance(true)->getUsedProducts($product);
        foreach ($productWithAttributes as $usedProduct) {
            $flag = 0;
            foreach ($attributeArray as $attrId => $attributeCode) {
                if (array_key_exists($attributeCode, $usedProduct->getData())) {
                    if (in_array($usedProduct[$attributeCode], $configurableOptions)) {
                        $productIdArray[] = $usedProduct->getEntityId();
                    }
                }
            }
        }
        $counts = array_count_values($productIdArray);
        arsort($counts);
        $configurableProductId = key($counts);
        $configurableProduct = $this->getProduct($configurableProductId);
        $finalPrice += $configurableProduct->getPrice();
        foreach ($configurableOptions as $attrId => $configurableOption) {
                $attr = $this->loadData($this->_attribute, $attrId);
                $label = $attr->getSource()->getOptionText($configurableOption);
                $optionString .= "<br><dt style='display:inline-block'>".$attr->getFrontendLabel().'</dt>: ';
                $optionString .= "<dd style='display:inline-block'>".$label.'</dd><br>';
        }
        return [$optionString, $finalPrice];
    }
    
    /**
     * GetConfigurableProductQuantity
     *
     * @param int   $product
     * @param array $quote
     * @return int
     */
    public function getConfigurableProductQuantity($product, $quote)
    {
        $productQty = 0;
        $configurableOptions = $this->convertStringAccToVersion(
            $quote->getSuperAttribute(),
            'decode'
        );
        
        $childProducts = $this->_typeConfigurable->getUsedProducts($product);
        foreach ($childProducts as $child) {
            foreach ($configurableOptions as $key => $configurableOption) {
                $configattr = $child->getData($this->loadData($this->_attribute, $key)->getAttributeCode());
                if ($configattr == $configurableOption) {
                    $childProduct = $this->getProduct($child->getEntityId());
                    $productQty = $childProduct->getQuantityAndStockStatus()['qty'];
                }
            }
        }
        return $productQty;
    }
    
    /**
     * ValidateBundleProductQuantity
     *
     * @param object $product
     * @param array  $bundleOptions
     * @param object $quote
     * @param array  $wholedata
     * @return boolean
     */
    public function validateBundleProductQuantity($product, $bundleOptions, $quote, $wholedata)
    {
        $quantity = 0;
        $selectionCollection = $product->getTypeInstance(true)
            ->getSelectionsCollection(
                $product->getTypeInstance(true)->getOptionsIds($product),
                $product
            );
        $optionsCollection = $product->getTypeInstance(true)
            ->getOptionsCollection($product);
        foreach ($optionsCollection as $options) {
            $optionId = $options->getOptionId();
            if (array_key_exists($optionId, $bundleOptions['bundle_option'])
                && $valueId = $bundleOptions['bundle_option'][$optionId]
            ) {
                foreach ($selectionCollection as $proselection) {
                    $selectedProductId = 0;
                    if (is_array($valueId)) {
                        if (in_array($proselection->getSelectionId(), $valueId)) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    } else {
                        if ($proselection->getSelectionId() == $valueId) {
                            $selectedProductId = $proselection->getEntityId();
                        }
                    }
                    if ($selectedProductId) {
                        $selectedProduct = $this->getProduct($selectedProductId);
                        $quantity = $selectedProduct->getQuantityAndStockStatus()['qty'];
                        $bundleProductQty = $bundleOptions['bundle_option_qty'][$optionId];
                        $checkQuantity = $quote->getQuoteQty();
                        if (array_key_exists('quote_qty', $wholedata) && $checkQuantity != $wholedata['quote_qty']) {
                            $checkQuantity = $wholedata['quote_qty'];
                        }
                        if ($bundleProductQty * $checkQuantity > $quantity) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }

    /**
     * CheckProductCanShowOrNot
     *
     * @param array  $product
     * @param string $where
     * @return boolean
     */
    public function checkProductCanShowOrNot($product, $where = 'catalog')
    {
        return $this->catalogProductHelper->canShow($product, $where);
    }
    
    /**
     * ValidateQuoteForCustomer
     *
     * @param int    $quoteId
     * @param int    $customerId
     * @param object $quoteModel
     * @return boolean
     */
    public function validateQuoteForCustomer($quoteId, $customerId, $quoteModel = null)
    {
        if ($quoteModel==null) {
            $quoteModel = $this->getWkQuoteModel()->load($quoteId);
        }
        if ($quoteModel && $quoteModel->getCustomerId()==$customerId) {
            return true;
        }
        return false;
    }
    
    /**
     * GetDiscountEnable
     *
     * @return boolean
     */
    public function getDiscountEnable()
    {
        return $this->scopeConfig->getValue(
            'mpquotesystem/quotesystem_settings/discount_enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * CheckQuoteProductIsInCart
     *
     * @return boolean
     */
    public function checkQuoteProductIsInCart()
    {
        if ($this->_customerSession->isLoggedIn()) {
            $cart = $this->_checkoutSession
                ->getQuote()
                ->getAllItems();
            $productIds = [];
            if (!empty($cart)) {
                foreach ($cart as $item) {
                    $result = $this->checkQuoteProductinItem($item);
                    if ($result) {
                        return true;
                    }
                }
                return false;
            }
        }
        return false;
    }

    /**
     * CheckAndUpdateForDiscount
     *
     * @param object $item
     * @return boolean
     */
    public function checkAndUpdateForDiscount($item)
    {
        $result = $this->checkQuoteProductinItem($item);
        if ($result && !$this->getDiscountEnable()) {
            return true;
        }
        return false;
    }

    /**
     * GetQuoteconfig
     *
     * @return object
     */
    public function getQuoteconfig()
    {
        return $this->quoteconfigFactory->create();
    }
    
    /**
     * GetCustomerId
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->mpHelper->getCustomerId();
    }

    /**
     * Get global cates
     *
     * @return array
     */
    public function getGlobalQuoteCates()
    {
        return $this->scopeConfig->getValue(
            self::PATH_QUOTE_CONFIG.'/quote_cates',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Checks if ShowPriceAfterLogin System Module is active
     *
     * @return boolean
     */
    public function isShowPriceAfterLoginEnabled()
    {
        return $this->moduleManager->isOutputEnabled("Webkul_ShowPriceAfterLogin");
    }

    /**
     * Function to get Add To Cart config value
     *
     * @return boolean
     */
    public function getConfigAddToCart()
    {
        if ($this->getQuoteEnabled()) {
            return $this->scopeConfig->getValue(
                'mpquotesystem/quotesystem_settings/allowed_add_to_cart',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return false;
    }

    /**
     * Get min qty
     *
     * @return int
     */
    public function getGlobalMinQty()
    {
        return $this->scopeConfig->getValue(
            self::PATH_QUOTE_CONFIG.'/min_qty',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get allowed type of attached
     *
     * @return string
     */
    public function getAllowedTypes()
    {
        return $this->scopeConfig->getValue(
            self::PATH_QUOTE_CONFIG.'/allowed_type',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get base currency price from current currency
     *
     * @param [float]   $price
     * @param [type]    $from
     * @return float
     */
    public function getBaseCurrencyPrice($price, $from = null)
    {
        if (!$from) {
            /*
            * Get Current Store Currency Rate
            */
            $currentCurrencyCode = $this->getCurrentCurrencyCode();
        } else {
            $currentCurrencyCode = $from;
        }
        $baseCurrencyCode = $this->getBaseCurrencyCode();
        $allowedCurrencies = $this->getConfigAllowCurrencies();
        $rates = $this->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }
        return $price / $rates[$currentCurrencyCode];
    }

    /**
     * Get current store currency
     *
     * @return string currencycode
     */
    public function getCurrentCurrency()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCurrencyCode();
    }

    /**
     * Get Seller Quote Config
     *
     * @param array $product
     *
     * @return array
     */
    public function getSellerQuoteConfig($product)
    {
        $customerId = $this->mpHelper
            ->getSellerProductDataByProductId($product->getId())
            ->setPageSize(1)->getFirstItem()->getSellerId();
        $quoteConfig = $this->getQuoteconfig()
            ->getCollection()
            ->addFieldToFilter('seller_id', $customerId)
            ->setPageSize(1)->getFirstItem();
        if ($quoteConfig->getId()) {
            return [
                'cates'     => explode(',', $quoteConfig->getCategories()),
                'min_qty'   => $quoteConfig->getMinQty()
            ];
        }
        return [];
    }

    /**
     * Get global quote config
     *
     * @return array
     */
    public function getGlobalQuoteConfig()
    {
        $globalQuote = $this->getGlobalQuoteCates();
        $minQty = $this->getGlobalMinQty();
        if ($globalQuote && $minQty) {
            return [
                'cates'     => explode(',', $globalQuote),
                'min_qty'   => $minQty
            ];
        }
        return [];
    }

    /**
     * Check product lie on quote categories or not
     *
     * @param object $product
     * @return int
     */
    public function applyGlobalConfig($product)
    {
        $status = $product->getQuoteStatus();
        if ($status == '0') {
            return false;
        }
        $proCates = $product->getCategoryIds();
        $sellerQuoteConfig = $this->getSellerQuoteConfig($product);
        $globalConfig = $this->getGlobalQuoteConfig();
        
        if (!empty($sellerQuoteConfig) && array_intersect($proCates, $sellerQuoteConfig['cates'])) {
            return $sellerQuoteConfig['min_qty'];
        } elseif (!empty($globalConfig) && array_intersect($proCates, $globalConfig['cates'])) {
            return $globalConfig['min_qty'];
        }
        return false;
    }

    /**
     * Check product has quote or not
     *
     * @param object $product
     * @return bool| array
     */
    public function checkProductHasQuote($product)
    {
        $status = $product->getQuoteStatus();
        $auctionModuleStatus = $this->checkModuleIsEnabledOrNot('Webkul_MpAuction');
        if ($auctionModuleStatus) {
            $auctionValues = $product->getAuctionType();
            $auctionOpt = explode(',', $auctionValues);
            if (in_array(2, $auctionOpt)) {
                return false;
            }
        }
        if ($status == 1 && $product->getTypeId()!='grouped') {
            $minQty = $product->getMinQuoteQty();
            if ($minQty == "" || $minQty == null) {
                $minQty = $this->applyGlobalConfig($product);
            }
            return [
                'min_qty' => $minQty
            ];
        } elseif ($status == 2 && $product->getTypeId()!='grouped') {
            $minQty = $this->applyGlobalConfig($product);
            return [
                'min_qty' => $minQty
            ];
        }
        return false;
    }

    /**
     * Return current currency symbol
     *
     * @return string
     */
    public function getCurrentCurrencyCodesymbol()
    {
        return $this->_localeCurrency->getCurrency(
            $this->getCurrentCurrencyCode()
        )->getSymbol();
    }

    /**
     * Get full url of attachment url
     *
     * @param string $path
     * @return string
     */
    public function getAttachFullUrl($path)
    {
        return $this->getMediaUrl()."wkquote/files".$path;
    }

    /**
     * SaveAttachment
     *
     * @return string
     */
    public function saveAttachment()
    {
        $savedFileName = '';
        $path = $this->fileSystem
            ->getDirectoryRead(DirectoryList::MEDIA)
            ->getAbsolutePath('wkquote/files');
        $this->fileDriver->createDirectory($path, 0755);
        $allowedType = $this->getAllowedTypes();
        try {
            $allowedExtensions = explode(',', $allowedType);
            $uploader = $this->fileUploaderFactory->create(['fileId' => 'quote_attachment']);
            $uploader->setAllowedExtensions($allowedExtensions);
            $uploader->validateFile();
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(true);
            $resultData = $uploader->save($path);
            unset($resultData['tmp_name']);
            unset($resultData['path']);
            $savedFileName = $resultData['file'];
        } catch (\Exception $e) {
            $error = true;
        }
        return $savedFileName;
    }

    /**
     * ValidateFiles
     *
     * @param  array $files
     * @return array
     */
    public function validateFiles($files)
    {
        $errors = [];
        if (!empty($files)) {
            foreach ($files as $name => $data) {
                $fileInfo = finfo_open(FILEINFO_MIME_TYPE);
                $mimeType = finfo_file($fileInfo, $data['tmp_name']);
                if (!empty($data['tmp_name'])
                    && (!empty($data['type']))
                ) {
                    if (strpos($data['type'], 'image') !== false) {
                        $isValid = false;
                        if ($data['size'] > 0) {
                            $isValid = true;
                        }
                        if ($isValid == false && strpos($mimeType, 'image') === false) {
                            $errors[] = __("%1 is not a valid image file", $data['name']);
                        }
                    } elseif ($data['type'] == "application/pdf" && $mimeType!=="application/pdf") {
                        $errors[] = __("%1 is not a valid pdf file", $data['name']);
                    } elseif ($data['type'] == "application/msword" && $mimeType!=="application/msword") {
                        $errors[] = __("%1 is not a valid doc file", $data['name']);
                    }
                }
            }
        }
        return $errors;
    }

    /**
     * To commit the object
     *
     * @param object $args
     * @return void
     */
    public function commitMethod($args)
    {
        $args->save();
    }

    /**
     * To delete the object
     *
     * @param object $args
     * @return object
     */
    public function deleteMethod($args)
    {
        $args->delete();
    }
    
    /**
     * UpdateFinalPrice
     *
     * @param int    $finalPrice
     * @param object $proselection
     * @param int    $valueId
     * @return int
     */
    public function updateFinalPrice($finalPrice, $proselection, $valueId)
    {
        if (in_array($proselection->getSelectionId(), $valueId)) {
            $optArray[] = '1 x '.
                $proselection->getName().
                ' '.
                $this->getformattedPrice(
                    $proselection->getPrice(),
                    true,
                    false
                );
            $finalPrice += $proselection->getPrice();
        }
        return $finalPrice;
    }
    
    /**
     * Load data
     *
     * @param object $model
     * @param string $key
     *
     * @return object
     */
    public function loadData($model, $key)
    {
        return $model->load($key);
    }
    
    /**
     * AddStoreFilter
     *
     * @param object  $store
     * @param boolean $withAdmin
     * @return void
     */
    public function getJsonObject()
    {
        return $this->jsonHelper;
    }
    
    /**
     * AddStoreFilter
     *
     * @param object  $store
     * @param boolean $withAdmin
     * @return void
     */
    public function getLoadedProductCollection()
    {
        return $this->_listProduct->getLoadedProductCollection();
    }
}
