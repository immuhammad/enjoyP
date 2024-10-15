<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Controller\Payment;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class GetSession extends Action
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_jsonResultFactory;

    /**
     * @var eventManager
     */
    protected $_eventManager;

    /**
     * @param Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory
     * @param \Magento\Checkout\Model\Type\Onepage $onePage
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Webkul\Stripe\Helper\Data $helper
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Webkul\Stripe\Logger\Logger $logger
     * @param \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Event\Manager $eventManager
     */
    public function __construct(
        Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonResultFactory,
        \Magento\Checkout\Model\Type\Onepage $onePage,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Stripe\Helper\Data $helper,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Session\SessionManagerInterface $session,
        \Webkul\Stripe\Logger\Logger $logger,
        \Magento\Quote\Model\ResourceModel\Quote\Item\CollectionFactory $itemCollection,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Event\Manager $eventManager
    ) {
        $this->_jsonResultFactory = $jsonResultFactory;
        $this->checkoutSession = $checkoutSession;
        $this->session = $session;
        $this->storeManager = $storeManager;
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        $this->onePage = $onePage;
        $this->logger = $logger;
        $this->itemCollection = $itemCollection;
        $this->jsonHelper = $jsonHelper;
        $this->_eventManager = $eventManager;
        parent::__construct($context);
    }

    /**
     * Create stripe data for checkout page
     */
    public function execute()
    {
        $smallcurrencyarray = ["bif", "clp", "djf", "gnf", "jpy", "kmf", "krw", "mga", "pyg", "rwf",
                                    "vnd", "vuv", "xaf", "xof", "xpf"];
                                    
        $resultJson = $this->_jsonResultFactory->create();
        $resultJson->setHeader('Cache-Control', 'max-age=0, must-revalidate, no-cache, no-store', true);
        $resultJson->setHeader('Pragma', 'no-cache', true);

        \Stripe\Stripe::setApiKey($this->helper->getConfigValue("api_secret_key"));
        
        \Stripe\Stripe::setAppInfo(
            "Webkul Stripe Payment Gateway For Magento 2",
            "3.0.0",
            "https://store.webkul.com/magento2-stripe-payment-gateway.html",
            "pp_partner_FLJSvfbQDaJTyY"
        );
        \Stripe\Stripe::setApiVersion("2019-12-03");
        
        $params = $this->getRequest()->getParams();
        $quote = $this->checkoutSession->getQuote();
        if (isset($params['email']) && $params['email']!="") {
            $this->session->setStripeGuestUserEmail($params['email']);
        }

        $lineItems = [];
        $data = [];
        foreach ($quote->getAllVisibleItems() as $item) {
            $product = $item->getProductOptions();
            $stripeAmount = $this->getItemFinalAmount($item);
            if (in_array(strtolower($quote->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
                $stripeAmount = round($stripeAmount);
            } else {
                $stripeAmount = ($stripeAmount) * 100;
            }
            if ($stripeAmount) {
                $itemQty = (int) $item->getQty();
                $lineItems[] = [
                    "name" => $item->getName(),
                    "description" => $item->getName(),
                    "amount" => round($stripeAmount/$itemQty),
                    "currency" => $quote->getStore()->getCurrentCurrencyCode(),
                    "quantity" => $itemQty
                ];
            }
        }
        if (in_array(strtolower($quote->getStore()->getCurrentCurrencyCode()), $smallcurrencyarray)) {
            $stripeShippingAmount = round($quote->getShippingAddress()->getShippingAmount());
        } else {
            $stripeShippingAmount = $quote->getShippingAddress()->getShippingAmount() * 100;
        }
        if (!$quote->getIsVirtual() && $stripeShippingAmount) {
            $lineItems[] = [
                "name" => __("Shipping"),
                "description" => __("Products Shipping Cost"),
                "amount" => $stripeShippingAmount,
                "currency" => $quote->getStore()->getCurrentCurrencyCode(),
                "quantity" => 1
            ];
        }
        $stripeLineItems= new \Magento\Framework\DataObject(["stripe_line_items"=>$lineItems]);
        $this->_eventManager->dispatch(
            'stripe_checkout_line_items',
            ["stripe_request"=>$stripeLineItems]
        );

        $data = [
            "success_url" => $this->storeManager->getStore()->getUrl(
                'stripe/payment/success',
                ['quote_id' => $quote->getId()]
            ),
            "cancel_url" => $this->storeManager->getStore()->getUrl('stripe/payment/failure'),
            "payment_method_types" => explode(',', $this->helper->getConfigValue("payment_method_types")),
            "client_reference_id" => $quote->getId(),
            "customer_email" => $quote->getCustomerEmail()??$params['email'],
            "line_items" => $stripeLineItems->getStripeLineItems()
        ];
        $stripeData= new \Magento\Framework\DataObject(["stripe_data"=>$data]);
        $this->_eventManager->dispatch(
            'stripe_checkout_data',
            ["stripe_request_data"=>$stripeData]
        );
        
        try {
            $response = \Stripe\Checkout\Session::create($stripeData->getStripeData());
            $this->logger->info('Stripe Session response try '.$this->jsonHelper->jsonEncode($response));
        } catch (\Exception $e) {
            $this->logger->info('Stripe Session response catch '.$e->getMessage());
            $response = [];
        }

        return $resultJson->setData($response);
    }

    /**
     * Get item total amount
     *
     * @param object $item
     * @return int
     */
    public function getItemFinalAmount($item)
    {
        $itemAmount = 0;
        $itemAmount += $item->getRowTotal() - $item->getDiscountAmount() + $item->getTaxAmount();

        if ($item->getProductType() == 'configurable') {
            $itemAmount = 0;
            $itemAmount += $item->getRowTotal() - $item->getDiscountAmount() + $item->getTaxAmount();
            
            $quoteChildItem = $this->itemCollection->create()
            ->addFieldToFilter('parent_item_id', $item->getItemId());
            foreach ($quoteChildItem as $childItem) {
                $itemAmount += $childItem->getRowTotal() - $childItem->getDiscountAmount() + $childItem->getTaxAmount();
            }
        } elseif ($item->getProductType() == 'bundle') {
            $itemAmount = 0;
            $quoteChildItem = $this->itemCollection->create()
            ->addFieldToFilter('parent_item_id', $item->getItemId());
            foreach ($quoteChildItem as $childItem) {
                $itemAmount += $childItem->getRowTotal() - $childItem->getDiscountAmount() + $childItem->getTaxAmount();
            }
        }
        return $itemAmount;
    }
}
