<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpsplitorder
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpsplitorder\Model;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Model\Quote as QuoteEntity;
use Magento\Quote\Model\Quote\Address\ToOrder as ToOrderConverter;
use Magento\Quote\Model\Quote\Address\ToOrderAddress as ToOrderAddressConverter;
use Magento\Quote\Model\Quote\Item\ToOrderItem as ToOrderItemConverter;
use Magento\Quote\Model\Quote\Payment\ToOrderPayment as ToOrderPaymentConverter;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory as OrderFactory;
use Magento\Sales\Api\OrderManagementInterface as OrderManagement;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Session\SessionManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Quote\Api\Data\CurrencyInterface;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\Data\CustomerInterface;

class QuoteManagement extends \Magento\Quote\Model\QuoteManagement
{
    /**
     * @var Webkul\Mpmangopay\Helper\Data
     */
    protected $_mpSplitOrderHelper;

    /**
     * $checkoutSession.
     *
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Api\DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var SessionManager
     */
    protected $_coreSession;

    /**
     * @param EventManager $eventManager
     * @param QuoteValidator $quoteValidator
     * @param OrderFactory $orderFactory
     * @param OrderManagement $orderManagement
     * @param CustomerManagement $customerManagement
     * @param ToOrderConverter $quoteAddressToOrder
     * @param ToOrderAddressConverter $quoteAddressToOrderAddress
     * @param ToOrderItemConverter $quoteItemToOrderItem
     * @param ToOrderPaymentConverter $quotePaymentToOrderPayment
     * @param UserContextInterface $userContext
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Magento\Customer\Model\CustomerFactory $customerModelFactory
     * @param \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
     * @param \Magento\Framework\Api\DataObjectHelper $dataObjectHelper
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Api\AccountManagementInterface $accountManagement
     * @param QuoteFactory $quoteFactory
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Store\Model\StoreManagerInterface              $storeManager
     * @param \Magento\Framework\UrlInterface                         $urlBuilder
     * @param \Magento\Checkout\Model\Session                         $checkoutSession
     * @param \Magento\Framework\ObjectManagerInterface               $objectManager
     */

    public function __construct(
        EventManager $eventManager,
        \Magento\Quote\Model\SubmitQuoteValidator $quoteValidator,
        OrderFactory $orderFactory,
        OrderManagement $orderManagement,
        \Magento\Quote\Model\CustomerManagement $customerManagement,
        ToOrderConverter $quoteAddressToOrder,
        ToOrderAddressConverter $quoteAddressToOrderAddress,
        ToOrderItemConverter $quoteItemToOrderItem,
        ToOrderPaymentConverter $quotePaymentToOrderPayment,
        UserContextInterface $userContext,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\CustomerFactory $customerModelFactory,
        \Magento\Quote\Model\Quote\AddressFactory $quoteAddressFactory,
        \Magento\Framework\Api\DataObjectHelper $dataObjectHelper,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\AccountManagementInterface $accountManagement,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Mpsplitorder\Helper\Data $mpSplitOrderHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Checkout\Model\Session $checkoutSession,
        SessionManager $coreSession,
        DateTime $date,
        \Webkul\Marketplace\Model\ProductFactory $mpProductFactory,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Mpsplitorder\Model\MpsplitorderFactory $mpsplitorderFactory,
        \Magento\Customer\Model\AddressFactory $addressFactory,
        \Webkul\Mpsplitorder\Logger\Mpsplitorder $splitorderLogger,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepository = null,
        \Magento\Quote\Model\QuoteIdMaskFactory $quoteIdMaskFactory = null,
        \Magento\Framework\App\RequestInterface $request = null,
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress = null
    ) {
        $this->_objectManager = $objectManager;
        $this->_mpSplitOrderHelper = $mpSplitOrderHelper;
        $this->_messageManager = $messageManager;
        $this->_urlBuilder = $urlBuilder;
        $this->checkoutSession = $checkoutSession;
        $this->_coreSession = $coreSession;
        $this->_date = $date;
        $this->quoteFactory = $quoteFactory;
        $this->mpProductFactory = $mpProductFactory;
        $this->productFactory = $productFactory;
        $this->mpsplitorderFactory = $mpsplitorderFactory;
        $this->addressFactory = $addressFactory;
        $this->splitorderLogger = $splitorderLogger;
        $this->addressRepository = $addressRepository ?: ObjectManager::getInstance()
            ->get(\Magento\Customer\Api\AddressRepositoryInterface::class);
        
        parent::__construct(
            $eventManager,
            $quoteValidator,
            $orderFactory,
            $orderManagement,
            $customerManagement,
            $quoteAddressToOrder,
            $quoteAddressToOrderAddress,
            $quoteItemToOrderItem,
            $quotePaymentToOrderPayment,
            $userContext,
            $quoteRepository,
            $customerRepository,
            $customerModelFactory,
            $quoteAddressFactory,
            $dataObjectHelper,
            $storeManager,
            $checkoutSession,
            $customerSession,
            $accountManagement,
            $quoteFactory,
            $quoteIdMaskFactory,
            $addressRepository,
            $request,
            $remoteAddress
        );
    }

    public function placeOrder($cartId, PaymentInterface $paymentMethod = null)
    {
        try {
          
            $this->_coreSession->setData('split_sellers', 0);
            $shippingAll = $this->_coreSession->getShippingInfo();
            $quote = $this->checkoutSession->getQuote();
            $this->_coreSession->setData('grand_total', $quote->getGrandTotal());
            
            $discount = $quote->getSubtotal() - $quote->getSubtotalWithDiscount();
            $couponCode = $quote->getCouponCode();
            $percent = round(($discount*100)/$quote->getSubtotal(), 2);
            //Check module enable

            if ($this->_mpSplitOrderHelper->getIsActive() == 0) {
                $orderId = parent::placeOrder($quote->getId());
                return $orderId;
            }
            $finalArray = [];
            $itemArray = [];
            $splitShip = 0;

            foreach ($quote->getAllItems() as $item) {
                $request = [];
                $shippable = 0;
                if ($item->getParentItem()) {
                    continue;
                } else {
                    if (!in_array($item->getProductType(), ["virtual", "downloadable"])) {
                        $shippable = $item->getQty();
                        $splitShip += $item->getQty();
                    }
                    foreach ($item->getOptions() as $option) {
                        if ($option->getCode()=="info_buyRequest") {
                            $value = json_decode($option->getValue(), true);
                            $value['qty'] = $item->getQty();
                            $request[]  = $value;
                        }
                    }
                }

                $id = 0;
                $rowTotal = $item->getRowTotal();
                $marketplaceCollection = $this->mpProductFactory->create()
                    ->getCollection()
                    ->addFieldToFilter(
                        'mageproduct_id',
                        $item->getProductId()
                    );
                foreach ($marketplaceCollection as $vendor) {
                    $id = $vendor->getSellerId();
                }

                //Check Assign Seller
                if (isset($request[0]['mpassignproduct_id']) && $request[0]['mpassignproduct_id']!="") {
                    $sellerId = $this->_objectManager->create(
                        \Webkul\MpAssignProduct\Helper\Data::class
                    )->getAssignSellerIdByAssignId($request[0]['mpassignproduct_id']);
                    $id = $sellerId;
                    if (isset($request[0]['associate_id']) && $request[0]['associate_id']) {
                        $price = $this->_objectManager->create(
                            \Webkul\MpAssignProduct\Helper\Data::class
                        )->getAssocitePrice($request[0]['mpassignproduct_id'], $request[0]['associate_id']);
                    } else {
                        $price = $this->_objectManager->create(
                            \Webkul\MpAssignProduct\Helper\Data::class
                        )->getAssignProductPrice($request[0]['mpassignproduct_id']);
                    }
                    $request[0]['price'] = $price;
                    $rowTotal = $price;
                }

                $finalArray[$id][] = [
                    'product' => $item->getProductId(),
                    'request' => $request[0],
                    'qty' => $item->getQty(),
                    'item_id' => $item->getId(),
                    'discount' => $item->getDiscountAmount(),
                    'base_discount' => $item->getBaseDiscountAmount(),
                    'shippable' => $shippable,
                    'price' => $item->getPrice(),
                ];

                $itemArray[] = [
                    'id' => $item->getId(),
                    'row_total' => $rowTotal,
                    'product_id' => $item->getProductId(),
                    'tax_amount' => $item->getTaxAmount(),
                    'seller_id' => $id
                ];
            }

            $this->_coreSession->setData('split_sellers', count($finalArray));

            // Check only one seller product

            if (count($finalArray) == 1) {
                $this->_coreSession->setData('discount_description', $couponCode);
                // Collect Totals & Save Quote
                $quote->collectTotals()->save();
                $orderId = parent::placeOrder($quote->getId());
                return $orderId;
            } else {
                $orderedArray = [
                'shipping_method' => $quote->getShippingAddress()->getShippingMethod(),
                'shipping_tax_amount' => $quote->getShippingAddress()->getData('shipping_tax_amount'),
                'shipping_amount' =>$quote->getShippingAddress()->getShippingAmount(),
                ];
                $currenQuoteId=$this->checkoutSession->getQuoteId();
                $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();
                $currency = $quote->getCurrency();
                $billingAddress1 = $quote->getBillingAddress()->getData();
                $shippingAddress1 = $quote->getShippingAddress()->getData();
                if (!isset($billingAddress1['postcode'])) {
                    $billingAddress1['postcode'] = '';
                }
                if (!isset($shippingAddress1['postcode'])) {
                    $shippingAddress1['postcode'] = '';
                }
                if ($splitShip) {
                    $shippingAmountPerItem = $shippingAddress1['base_shipping_amount'] / $splitShip;
                } else {
                    $shippingAmountPerItem = 0;
                }
                $checkoutMethod = $quote->getCheckoutMethod();
                $this->checkoutMethd=$checkoutMethod;
                $paymentMethod = $quote->getPayment()->getMethod();
                $quote->setIsActive(0)->delete()->save();
                $quote = $this->quoteFactory->create()->load($currenQuoteId);
                $quote->setIsActive(0)->delete();
                $orderIds = [];
                $store = $this->storeManager->getStore();
                $customer = $this->checkCustomer($billingAddress1);
                $this->customr=$customer;
                $this->_coreSession->setIsSpiltOrder(1);
                $mpSelectedMethods = $this->_coreSession->getSelectedMethods();

                foreach ($finalArray as $sId => $items) {
                    $this->shippingAmount = 0;
                    $this->_coreSession->unsMpAssignItemId();
                    // Start New Sales Order Quote
                    $newquote = $this->quoteFactory->create();
                    $newquote->setStore($store);
                    // Set Sales Order Quote Currency
                    $newquote->setCurrency($currency);
                    $newquote->setCheckoutMethod($checkoutMethod);
                    // Assign Customer To Sales Order Quote
                    // Configure Notification
                    $newquote->setSendCconfirmation(1);

                    $this->tempDiscount = 0;
                    $this->tempBaseDiscount = 0;
                    $this->itemIdsMap = [];
                    $this->prevItemIds = [];
                    $this->discounts = [];
                    $this->baseDiscounts = [];
                    $this->priceArr = [];
                    
                    $this->assigneItem($items, $newquote, $this->shippingAmount, $shippingAmountPerItem);
            
                    // Set Sales Order Billing Address
                    $billingAddress = $newquote->getBillingAddress()->addData(
                        [
                            'customer_id' => $billingAddress1['customer_id'],
                            'address_type' => $billingAddress1['address_type'],
                            'firstname' => $billingAddress1['firstname'],
                            'lastname' =>$billingAddress1['lastname'],
                            'email' => $billingAddress1['email'],
                            'street' => $billingAddress1['street'],
                            'city' => $billingAddress1['city'],
                            'country_id' => $billingAddress1['country_id'],
                            'region_id' => $billingAddress1['region_id'],
                            'postcode' => $billingAddress1['postcode'],
                            'telephone' => $billingAddress1['telephone'],
                            'save_in_address_book' => $billingAddress1['save_in_address_book'],
                            'same_as_billing' => $billingAddress1['same_as_billing'],
                        ]
                    );
                    // Set Sales Order Shipping Address

                    $shippingAddress = $newquote->getShippingAddress()->addData(
                        [
                            'customer_id' => $shippingAddress1['customer_id'],
                            'address_type' => $shippingAddress1['address_type'],
                            'firstname' => $shippingAddress1['firstname'],
                            'lastname' =>$shippingAddress1['lastname'],
                            'email' => $shippingAddress1['email'],
                            'street' => $shippingAddress1['street'],
                            'city' => $shippingAddress1['city'],
                            'country_id' => $shippingAddress1['country_id'],
                            'region_id' => $shippingAddress1['region_id'],
                            'postcode' => $shippingAddress1['postcode'],
                            'telephone' => $shippingAddress1['telephone'],
                            'same_as_billing' => $shippingAddress1['same_as_billing'],
                        ]
                    );

                    if ($checkoutMethod == 'guest') {

                        $newquote->setCustomerId(null)
                            ->setCustomerEmail($billingAddress1['email'])
                            ->setCustomerIsGuest(true)
                            ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

                    } else {
                        $newquote->assignCustomerWithAddressChange($customer, $billingAddress, $shippingAddress);
                    }

                    // Collect Rates and Set Shipping & Payment Method

                    if ($shippingAddress1['shipping_method']!='mpmultishipping_mpmultishipping') {
                        if (!empty($shippingAll)) {
                            $this-> setspMethod($shippingAddress1, $shippingAll, $mpSelectedMethods, $sId);
                        } else {
                            $shippingData = [];
                            $shippingData = [
                                'title' => $shippingAddress1['shipping_description'],
                                'cost'  => $this->shippingAmount
                            ];
                            $this->checkoutSession->setData('shippingInfo', $shippingData);
                            $shippingMethod = 'splitship_splitship';
                            $shippingAddress1['shipping_method']="splitship_splitship";
                        }
                    } else {
                        if (is_array($mpSelectedMethods) && !empty($mpSelectedMethods)) {
                            $this->calculateShippingAmount($mpSelectedMethods, $sId, $itemIdsMap);
                        }
                    }

                    $this->checkoutSession->replaceQuote($newquote);
                    $shippingAddress = $newquote->getShippingAddress();
                    $shippingAddress->setCollectShippingRates(true)
                                    ->collectShippingRates()
                                    ->setShippingMethod($shippingAddress1['shipping_method']);

                    $newquote->setPaymentMethod($paymentMethod);
                    $newquote->setInventoryProcessed(false);
                    $newquote->save();
                    $address = $newquote->getShippingAddress();
                    $rates = $address->collectShippingRates()
                    ->getGroupedAllShippingRates();
                    foreach ($rates as $carrier) {
                        foreach ($carrier as $rate) {
                            $rate->setPrice($this->shippingAmount);
                            $rate->save();
                        }
                    }
                    $address->setCollectShippingRates(false);
                    $address->save();
                    // Set Sales Order Payment
                    $newquote->getPayment()->importData(['method' => $paymentMethod]);
                    $newquote->setIsActive(1);
                    $newquote->setCustomDiscount($this->tempDiscount);
                    $newquote->setBaseCustomDiscount($this->tempBaseDiscount);
                    $this->_coreSession->setData('discount_description', $couponCode);
                    $this->_coreSession->setData('item_discount', $this->discounts);
                    $this->_coreSession->setData('item_base_discount', $this->baseDiscounts);
                    // Collect Totals & Save Quote
                    $newquote->collectTotals()->save();

                    // Create Order From Quote
                    $newquote = $this->quoteRepository->get($newquote->getId());
                    $orderId = parent::placeOrder($newquote->getId());
                    $orderIds[] = $orderId;
                    $lastOrderId = $orderId;
                    $newquote->removeAllItems()->save();
                    $newquote = $service = null;
                }

                $quote = $this->checkoutSession->getQuote();
                $this->checkoutSession->setData('shippingInfo', 0);
                $this->_coreSession->setData('orderids', implode(',', $orderIds));
                $this->_coreSession->setData('item', $itemArray);
                $this->_coreSession->setData('shipping_info', $orderedArray);
                $this->_coreSession->setData('lastOrderId', $lastOrderId);
                $quote->removeAllItems()->save();

                //save data in Mpsplitorder table
                $splitOrderCollection = $this->mpsplitorderFactory->create();
                $splitOrderCollection->setOrderIds(implode(',', $orderIds));
                $splitOrderCollection->setLastOrderId($lastOrderId);
                $this->_coreSession->unsIsSpiltOrder();
                $splitOrderCollection->setPaymentStatus(0);
                $splitOrderCollection->save();
            }
        } catch (\Exception $e) {
            $this->splitorderLogger->info($e->getMessage());
            $this->splitorderLogger->info($e->getTraceAsString());
            $this->_messageManager->addError($e->getMessage());
            $this->_coreSession->unsIsSpiltOrder();
        }
    }
    public function calculateShippingAmount($mpSelectedMethods, $sId, $itemIdsMap)
    {
        $this->shippingAmount = 0;
            $newmpSelectedMethods = [];
        foreach ($mpSelectedMethods as $value1) {
            $value1 = (array) $value1;
            if ($sId == $value1['sellerid']) {
                $this->shippingAmount += $value1['baseamount'];
                if (isset($itemIdsMap[$value1['itemid']]) && $itemIdsMap[$value1['itemid']]) {
                    $value1['itemid'] = $itemIdsMap[$value1['itemid']];
                }
                $newmpSelectedMethods[] = $value1;
            }
        }
            $this->_coreSession->setSelectedMethods($newmpSelectedMethods);
    }

    public function setspMethod($shippingAddress1, $shippingAll, $mpSelectedMethods, $sId)
    {
        try {
            $isMpShipping = $this->isMpShipping($shippingAddress1['shipping_method']);
            if (is_array($isMpShipping)) {
                $subMethod = $isMpShipping[0];
                $method = $isMpShipping[1];
                foreach ((array)$shippingAll[$method] as $key1 => $value1) {
                    if ($sId == $value1['seller_id']) {
                        $this->shippingAmount = $value1['submethod'][$subMethod]['base_amount'];
                        break;
                    }
                }
            } elseif (strpos($shippingAddress1['shipping_method'], "wkpickup")!==false) {
                $selectedWkpickups = $this->_coreSession->getSelectedPickupMethods();
                if (is_array($selectedWkpickups) && !empty($selectedWkpickups)) {
                    $this->shippingAmount = 0;
                    foreach ($selectedWkpickups as $value1) {
                        $value1 = (array) $value1;
                        if ($sId == $value1['sellerid']) {
                            $this->shippingAmount += $value1['baseamount'];
                        }
                    }
                }
            } else {
                $shippingData = [];
                $shippingData = [
                'title' => $shippingAddress1['shipping_description'],
                'cost'  => $this->shippingAmount
                ];
                $this->checkoutSession->setData('shippingInfo', $shippingData);
                $shippingMethod = 'splitship_splitship';
                $shippingAddress1['shipping_method']="splitship_splitship";
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    public function assigneItem($items, $newquote, $shippingAmount, $shippingAmountPerItem)
    {
        $this->tempDiscount = 0;

        $this->tempBaseDiscount = 0;
        $this->itemIdsMap = [];
        $this->prevItemIds = [];
        $this->discounts = [];
        $this->baseDiscounts = [];
        $this->priceArr = [];
        $this->shippingAmount=$shippingAmount;

        foreach ($items as $item) {

            $this->tempDiscount += floatval($item['discount']);
            $this->tempBaseDiscount += floatval($item['base_discount']);
            
            if (isset($item['shippable']) && $item['shippable']) {
                $this->shippingAmount += $item['shippable']*$shippingAmountPerItem;
            }
            
            $product = $this->productFactory->create()->load($item['product']);
            
            if (isset($item['request']['mpassignproduct_id']) &&
             $item['request']['mpassignproduct_id']!="") {
                $product->setPrice($item['request']['price']);
                $this->_coreSession->setMpAssignItemId($item['item_id']);
            }

            //check for Advertisment product
            foreach ($item['request'] as $req) {
                if (isset($req['block_position']) && $req['block_position']!="") {
                    $product->setPrice($req['price']);
                }
            }

            if (isset($item['request']['options'])) {
                foreach ($item['request']['options'] as $tempKey => $tempOptions) {
                    if (isset($tempOptions['date_internal'])) {
                        $item['request']['options'][$tempKey] = $tempOptions['date_internal'];
                    }
                }
            }

            $tempItemIds = [];

            $newquote->addProduct($product, new \Magento\Framework\DataObject($item['request']), 'full');
            if ($this->checkoutMethd == 'guest') {

                $newquote->setCustomerId(null)
                    ->setCustomerIsGuest(true)
                    ->setCustomerGroupId(GroupInterface::NOT_LOGGED_IN_ID);

            } else {
                $newquote->setCustsetCustomerId($this->customr->getId());
            }

            $newquote->save();

            foreach ($newquote->getAllItems() as $tempitem) {

                if (!$tempitem->getParentItem()) {

                    $tempItemIds[] = $tempitem->getId();
                    
                    $currentTempIds = array_diff($tempItemIds, $this->prevItemIds);
                    
                    if (!empty($currentTempIds)) {
                        $currentTempId = array_shift($currentTempIds);
                        $this->prevItemIds[] = $currentTempId;
                        $this->itemIdsMap[$item['item_id']] = $currentTempId;
                        $this->discounts[$currentTempId] = $item['discount'];
                        $this->baseDiscounts[$currentTempId] = $item['base_discount'];
                        $this->priceArr[$currentTempId] = $item['price'];
                    }
                }
            }
            foreach ($newquote->getAllItems() as $tempitem) {
                if (!$tempitem->getParentItem() && isset($priceArr[$tempitem->getId()])) {
                    $tempitem->setPrice($this->priceArr[$tempitem->getId()]);
                }
            }
        }
    }
    /**
     * checkCustomer Validate Customer
     * @param  Mixed $billingAddress1 Billing address from quote
     * @return Mage_Customer_Model
     */
    public function checkCustomer($billingAddress1)
    {
        $quote = $this->checkoutSession->getQuote();
        $email = $billingAddress1['email'];
        $firstname = $billingAddress1['firstname'];
        $lastname = $billingAddress1['lastname'];
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        $store = $this->storeManager->getStore();
        $customer=$this->customerModelFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($email);
        if ($customer->getId()=="") {
            if ($quote->getCheckoutMethod() == 'register') {
                     $customer->setWebsiteId($websiteId)
                    ->setStore($store)
                    ->setFirstname($firstname)
                    ->setLastname($lastname)
                    ->setEmail($email)
                    ->setPassword($email);
                    $customer->save();

                $this->customerSession->loginById($customer->getId());
                $customerAddress = $this->addressFactory->create();
                $cusAddress = [
                    'firstname' => $billingAddress1['firstname'],
                    'middlename' => $billingAddress1['middlename'],
                    'lastname' =>$billingAddress1['lastname'],
                    'email' => $billingAddress1['email'],
                    'suffix' => $billingAddress1['suffix'],
                    'street' => $billingAddress1['street'],
                    'city' => $billingAddress1['city'],
                    'country_id' => $billingAddress1['country_id'],
                    'region_id' => $billingAddress1['region_id'],
                    'postcode' => $billingAddress1['postcode'],
                    'telephone' => $billingAddress1['telephone'],
                    'fax' => $billingAddress1['fax'],
                ];
                $customerAddress->setData($cusAddress)
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');
                $customerAddress->save();
                $customer = $this->customerRepository->getById($customer->getId());
            }
        } elseif ($quote->getCheckoutMethod() != 'guest') {
            $customer = $this->customerRepository->getById($customer->getId());
            $billing = $quote->getBillingAddress();
            $shipping = $quote->isVirtual() ? null : $quote->getShippingAddress();

            $hasDefaultBilling = (bool)$customer->getDefaultBilling();
            $hasDefaultShipping = (bool)$customer->getDefaultShipping();

            if ($shipping && !$shipping->getSameAsBilling()
                && (!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())
            ) {
                $shippingAddress = $shipping->exportCustomerAddress();
                if (!$hasDefaultShipping) {
                    //Make provided address as default shipping address
                    $shippingAddress->setIsDefaultShipping(true);
                    $hasDefaultShipping = true;
                    if (!$hasDefaultBilling && !$billing->getSaveInAddressBook()) {
                        $shippingAddress->setIsDefaultBilling(true);
                        $hasDefaultBilling = true;
                    }
                }
                //save here new customer address
                $shippingAddress->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($shippingAddress);
                $quote->addCustomerAddress($shippingAddress);
                $shipping->setCustomerAddressData($shippingAddress);
                $shipping->setCustomerAddressId($shippingAddress->getId());
            }

            if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
                $billingAddress = $billing->exportCustomerAddress();
                if (!$hasDefaultBilling) {
                    //Make provided address as default shipping address
                    if (!$hasDefaultShipping) {
                        //Make provided address as default shipping address
                        $billingAddress->setIsDefaultShipping(true);
                    }
                    $billingAddress->setIsDefaultBilling(true);
                }
                $billingAddress->setCustomerId($quote->getCustomerId());
                $this->addressRepository->save($billingAddress);
                $quote->addCustomerAddress($billingAddress);
                $billing->setCustomerAddressData($billingAddress);
                $billing->setCustomerAddressId($billingAddress->getId());
            }
            if ($shipping && !$shipping->getCustomerId() && !$hasDefaultBilling) {
                $shipping->setIsDefaultBilling(true);
            }
        }
        return $customer;
    }

    public function isMpShipping($shippingMethod)
    {
        $subMethod = 0;
        $shippingWithSubmethod = ["mpfedex", "mpups", "marketplaceusps", "mpcanadapost",
                                 "mpfastway", "mpcorreios", "mparamex", "mpdhl", "mpfrenet", "webkulshipping"];
        $shippingWithoutSubmethod = ["mpfixrate", "webkulmpperproduct", "mppercountry", "mpfreeshipping"];

        foreach ($shippingWithoutSubmethod as $method) {
            if (strpos($shippingMethod, $method)!==false) {
                return [$subMethod, $method];
            }
        }
        foreach ($shippingWithSubmethod as $method) {
            if (strpos($shippingMethod, $method)!==false) {
                $subMethod = substr($shippingMethod, strpos($shippingMethod, "_")+1);
                return [$subMethod, $method];
            }
        }
        return false;
    }
}
