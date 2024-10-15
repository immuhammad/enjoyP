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
namespace Webkul\Mpperproductshipping\Model;

use Magento\Quote\Model\Quote\Address\RateResult\Error;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Session\SessionManager;
use Magento\Catalog\Model\ProductFactory;
use Magento\Quote\Model\Quote\Item\OptionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\AddressFactory;
use Webkul\MarketplaceBaseShipping\Model\ShippingSettingRepository;

class Carrier extends \Webkul\MarketplaceBaseShipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    const CODE = 'webkulmpperproduct';
    /**
     * Code of the carrier.
     *
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * Rate request data.
     *
     * @var \Magento\Quote\Model\Quote\Address\RateRequest|null
     */
    protected $_request = null;

    /**
     * Rate result data.
     *
     * @var Result|null
     */
    protected $_result = null;

    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Webkul\Mppercountryperproductshipping\Helper\Data
     */
    protected $_currentHelper;
    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;
    /**
     * Raw rate request data.
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $_rawRequest = null;
    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\DataObject|null
     */
    protected $baseRequest = null;
    /**
     * Raw rate request data
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_requestInterface = null;

    protected $_isFixed = true;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param SessionManager                                              $coreSession
     * @param \Magento\Checkout\Model\Session                             $checkoutSession
     * @param \Magento\Customer\Model\Session                             $customerSession
     * @param \Webkul\Mppercountryperproductshipping\Helper\Data          $currentHelper
     * @param array                                                       $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        SessionManager $coreSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Mpperproductshipping\Helper\Data $currentHelper,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\App\RequestInterface $requestInterface,
        PriceCurrencyInterface $priceCurrency,
        OptionFactory $optionFactory,
        CustomerFactory $customerFactory,
        AddressFactory $addressFactory,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        ProductFactory $productFactory,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        ShippingSettingRepository $shippingSettingRepository,
        array $data = []
    ) {
        $this->_currentHelper = $currentHelper;
        $this->productFactory = $productFactory;

        parent::__construct(
            $scopeConfig,
            $rateErrorFactory,
            $logger,
            $rateResultFactory,
            $rateMethodFactory,
            $regionFactory,
            $coreSession,
            $checkoutSession,
            $customerSession,
            $currencyFactory,
            $storeManager,
            $localeFormat,
            $jsonHelper,
            $requestInterface,
            $priceCurrency,
            $optionFactory,
            $customerFactory,
            $addressFactory,
            $mpProductFactory,
            $productFactory,
            $saleslistFactory,
            $shippingSettingRepository,
            $data
        );
    }

    public function getAllowedMethods()
    {
        return ['webkulmpperproduct' => $this->getConfigData('name')];
    }

    public function collectRates(RateRequest $request)
    {
        $this->baseRequest = $request;
        if (!$this->getConfigFlag('active') || $this->isMultiShippingActive()) {
            return false;
        }
        $this->setRequest($request);
        $shippingpricedetail = $this->getShippingPricedetail($this->_rawRequest);
        $result = $this->_rateResultFactory->create();
        if ($shippingpricedetail['errormsg'] !== '') {
            // Display error message if there
            $this->_errors[$this->_code] = $shippingpricedetail['errormsg'];
            $error = $this->_rateErrorFactory->create();
            $error->setCarrier($this->_code);
            $error->setCarrierTitle($this->getConfigData('title'));
            $error->setErrorMessage($shippingpricedetail['errormsg']);

            return $error;
        }
        /*store shipping in session*/
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier($this->_code);
        $method->setCarrierTitle($this->getConfigData('title'));
        /* Use method name */
        $method->setMethod($this->_code);
        $method->setMethodTitle($this->getConfigData('name'));
        $method->setCost($shippingpricedetail['handlingfee']);
        $method->setPrice($shippingpricedetail['handlingfee']);
        $result->append($method);

        return $result;
    }

    public function getShippingPricedetail(RateRequest $request)
    {
        $r = $request;
        $submethod = [];
        $shippinginfo = [];
        $handling = 0;
        $shippingbasedon = $this->getConfigData('shippingbasedon');
        foreach ($r->getShippingDetails() as $shipdetail) {
            $handlingFee = $this->getConfigData('handlingfee') ? $this->getConfigData('handlingfee') : 0;
            $price = 0;
            $itemsarray = explode(',', $shipdetail['item_id']);
            $itemPriceDetails = [];
            $allItems = $request->getAllItems();
            foreach ($allItems as $item) {
                $bundlePrice = 0;
                $itemId = $item->getId();
                $req = $this->requestInterface;
                if ($req->getModuleName() == 'multishipping' &&
                 $req->getControllerName() == 'checkout'
                 ) {
                    $itemId = $item->getQuoteItemId();
                }
                if (in_array($itemId, $itemsarray)) {
                    $itemPrice = 0;
                    if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                        continue;
                    }
                    if ($item->getHasChildren()) {
                        $_product = $this->productFactory->create()->load($item->getProductId());
                        if ($_product->getTypeId() == 'bundle') {
                            $mpshippingcharge = $_product->getMpShippingCharge();
                            $bundleType =
                            $this->getBundleType($mpshippingcharge, $price, $itemPrice, $item);
                            $price = $bundleType[0];
                            $itemPrice = $bundleType[1];
                        } elseif ($_product->getTypeId() == 'configurable') {
                            $mpshippingcharge = $_product->getMpShippingCharge();
                            $configurableType =
                            $this->getConfigurableType($mpshippingcharge, $price, $itemPrice, $item);
                            $price = $configurableType[0];
                            $itemPrice = $configurableType[1];
                        }
                    } else {
                        $product = $this->productFactory->create()->load($item->getProductId());
                        $mpshippingcharge = $product->getMpShippingCharge();
                        $shippingPriceDetails =
                        $this->getShippingItem($mpshippingcharge, $price, $itemPrice, $item);
                        $price = $shippingPriceDetails[0];
                        $itemPrice = $shippingPriceDetails[1];
                    }
                    $itemPriceDetails[$item->getId()] = $itemPrice;
                }
            }
            $shipdetail = $this->shippingItemDetails($shipdetail);
            $handling = $handling + $price + $handlingFee;
            $price += $handlingFee;
            $submethod = [
                [
                    'method' => $this->_currentHelper->getshippingTitle(),
                    'cost' => $price,
                    'base_amount' => $price,
                    'error' => 0,
                ],
            ];
            array_push(
                $shippinginfo,
                [
                    'seller_id' => $shipdetail['seller_id'],
                    'methodcode' => $this->_code,
                    'shipping_ammount' => $price,
                    'product_name' => $shipdetail['product_name'],
                    'submethod' => $submethod,
                    'item_ids' => $shipdetail['item_id'],
                    'item_price_details'=>$itemPriceDetails,
                    'item_id_details'=>$shipdetail['item_id_details'],
                    'item_name_details'=>$shipdetail['item_name_details'],
                    'item_qty_details'=>$shipdetail['item_qty_details']
                ]
            );
        }

        $result = [
            'handlingfee' => $handling,
            'shippinginfo' => $shippinginfo,
            'errormsg' => '',
        ];
        $shippingAll = $this->_coreSession->getShippingInfo();
        $shippingAll[$this->_code] = $result['shippinginfo'];
        $this->_coreSession->setShippingInfo($shippingAll);
        return $result;
    }

    /**
     * [getShippingItem description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getShippingItem($mpshippingcharge, $price, $itemPrice, $item)
    {
        if (floatval($mpshippingcharge) == 0) {
            $price = $price + (floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty()));
            $itemPrice = $itemPrice + (floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty()));
        } else {
            $price = $price + ($mpshippingcharge * floatval($item->getQty()));
            $itemPrice = $itemPrice + ($mpshippingcharge * floatval($item->getQty()));
        }
        return [$price,$itemPrice];
    }

    /**
     * [getShippingChildrenData description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getShippingChildrenData($mpshippingcharge, $price, $itemPrice, $item)
    {
        if (floatval($mpshippingcharge) == 0) {
            $price = $price + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
            $itemPrice = $itemPrice + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
        } else {
            $price = $price + ($mpshippingcharge * floatval($item->getQty()));
            $itemPrice = $itemPrice + ($mpshippingcharge * floatval($item->getQty()));
        }
        return [$price,$itemPrice];
    }

    /**
     * [getConfigurableData description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getConfigurableData($mpshippingcharge, $price, $itemPrice, $item)
    {
        if (floatval($mpshippingcharge) == 0) {
            $price = $price + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
            $itemPrice = $itemPrice + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
        } else {
            $price = $price + ($mpshippingcharge * floatval($item->getQty()));
            $itemPrice = $itemPrice + ($mpshippingcharge * floatval($item->getQty()));
        }
        return [$price,$itemPrice];
    }

    /**
     * [getBundleData description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getBundleData($mpshippingcharge, $price, $itemPrice, $item)
    {
        if (floatval($mpshippingcharge) == 0) {
            $price = $price + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
            $itemPrice = $itemPrice + (
                floatval($this->getConfigData('defaultprice')) *
                floatval($item->getQty())
            );
        } else {
            $price = $price + ($mpshippingcharge * floatval($item->getQty()));
            $itemPrice = $itemPrice + ($mpshippingcharge * floatval($item->getQty()));
        }
        return [$price,$itemPrice];
    }

    /**
     * [getBundleChildrenData description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $bundlePrice       [$bundlePrice description]
     * @param $child             [$child description]
     *
     * @return $bundlePrice
     */
    public function getBundleChildrenData($mpshippingcharge, $bundlePrice, $price, $itemPrice, $item)
    {
        foreach ($item->getChildren() as $child) {
            $mpshippingcharge = $this->productFactory->create()->load($child->getProductId())->getMpShippingCharge();
            if (floatval($mpshippingcharge) == 0) {
                $bundlePrice = $bundlePrice + (
                    floatval($this->getConfigData('defaultprice')) *
                    floatval($child->getQty())
                );
            } else {
                $bundlePrice = $bundlePrice + ($mpshippingcharge * floatval($child->getQty()));
            }
        }
        return $bundlePrice;
    }

    /**
     * [shippingItemDetails description]
     *
     * @param $shipdetail  [$shipdetail description]
     *
     * @return $shipdetail   [return description]
     */
    public function shippingItemDetails($shipdetail)
    {
        if (!isset($shipdetail['item_id_details'])) {
            $shipdetail['item_id_details'] = '';
        }
        if (!isset($shipdetail['item_name_details'])) {
            $shipdetail['item_name_details'] = '';
        }
        if (!isset($shipdetail['item_qty_details'])) {
            $shipdetail['item_qty_details'] = '';
        }
        return $shipdetail;
    }

    /**
     * [getConfigurableType description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getConfigurableType($mpshippingcharge, $price, $itemPrice, $item)
    {
        $shippingbasedon = $this->getConfigData('shippingbasedon');
        $_product = $this->productFactory->create()->load($item->getProductId());
        if ($shippingbasedon == 0) {
            $mpshippingcharge = $_product->getMpShippingCharge();
            $shippingConfigurableDetails =
            $this->getConfigurableData($mpshippingcharge, $price, $itemPrice, $item);
            $price = $shippingConfigurableDetails[0];
            $itemPrice = $shippingConfigurableDetails[1];
        } else {
            foreach ($item->getChildren() as $child) {
                $mpshippingcharge = $this->productFactory->create()
                                    ->load($child->getProductId())->getMpShippingCharge();
                $shippingChildrenDetails =
                $this->getShippingChildrenData($mpshippingcharge, $price, $itemPrice, $item);
                $price = $shippingChildrenDetails[0];
                $itemPrice = $shippingChildrenDetails[1];
                continue;
            }
        }
        return [$price,$itemPrice];
    }

    /**
     * [getBundleType description]
     *
     * @param $mpshippingcharge  [$mpshippingcharge description]
     * @param $price             [$price description]
     * @param $itemPrice         [$itemPrice description]
     * @param $item              [$item description]
     *
     * @return $price, $itemPrice
     */
    public function getBundleType($mpshippingcharge, $price, $itemPrice, $item)
    {
        $shippingbasedon = $this->getConfigData('shippingbasedon');
        if ($shippingbasedon == 0) {
            $shippingBundleDetails =
            $this->getBundleData($mpshippingcharge, $price, $itemPrice, $item);
            $price = $shippingBundleDetails[0];
            $itemPrice = $shippingBundleDetails[1];
        } else {
            $bundlePrice = 0;
            $bundleChildPrice =
            $this->getBundleChildrenData($mpshippingcharge, $bundlePrice, $price, $itemPrice, $item);
            $bundlePrice = $bundleChildPrice * floatval($item->getQty());
            $price = $price + $bundlePrice;
            $itemPrice = $itemPrice + $bundlePrice;
        }
        return [$price,$itemPrice];
    }
}
