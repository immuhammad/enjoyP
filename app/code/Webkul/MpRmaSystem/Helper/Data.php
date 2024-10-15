<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Helper;

use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\ResourceModel\Order\Invoice\Item\CollectionFactory as InvoiceItemCollection;
use Magento\Sales\Model\ResourceModel\Order\Shipment\Item\CollectionFactory as ShipmentItemCollection;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollection;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as OrdersCollection;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SalesListCollection;
use Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory as DetailsCollection;
use Webkul\MpRmaSystem\Model\ResourceModel\Reasons\CollectionFactory as ReasonsCollection;
use Webkul\MpRmaSystem\Model\ResourceModel\Conversation\CollectionFactory as ConversationCollection;
use Magento\Sales\Block\Order\Item\Renderer\DefaultRendererFactory;
use Magento\Framework\Locale\CurrencyInterface;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\CatalogInventory\Api\StockManagementInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Filesystem\Glob;
use Magento\Sales\Api\Data\CreditmemoInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\CreditmemoRepositoryInterface;
use Webkul\Marketplace\Model\SaleslistFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    public const NEW_RMA = 'new_rma';
    public const UPDATE_RMA = 'update_rma';
    public const RMA_MESSAGE = 'rma_message';

    public const RESOLUTION_REFUND = 1;
    public const RESOLUTION_REPLACE = 2;
    public const RESOLUTION_CANCEL = 3;

    public const RESOLUTION_REFUND_LABEL = 'Refund';
    public const RESOLUTION_REPLACE_LABEL = 'Replace';
    public const RESOLUTION_CANCEL_LABEL = 'Cancel Items';

    public const ORDER_NOT_DELIVERED = 0;
    public const ORDER_DELIVERED = 1;
    public const ORDER_NOT_APPLICABLE = 2;

    public const ORDER_DELIVERED_LABEL = 'Delivered';
    public const ORDER_NOT_DELIVERED_LABEL = 'Not Delivered';
    public const ORDER_NOT_APPLICABLE_LABEL = 'Not Applicable';

    public const SELLER_STATUS_PENDING = 0;
    public const SELLER_STATUS_PACKAGE_NOT_RECEIVED = 0;
    public const SELLER_STATUS_PACKAGE_RECEIVED = 1;
    public const SELLER_STATUS_PACKAGE_DISPATCHED = 2;
    public const SELLER_STATUS_SOLVED = 2;
    public const SELLER_STATUS_DECLINED = 3;
    public const SELLER_STATUS_ITEM_CANCELED = 4;

    public const FINAL_STATUS_PENDING = 0;
    public const FINAL_STATUS_CANCELED = 1;
    public const FINAL_STATUS_DECLINED = 2;
    public const FINAL_STATUS_SOLVED = 3;
    public const FINAL_STATUS_CLOSED = 4;

    public const RMA_STATUS_PENDING = 0;
    public const RMA_STATUS_PROCESSING = 1;
    public const RMA_STATUS_SOLVED = 2;
    public const RMA_STATUS_DECLINED = 3;
    public const RMA_STATUS_CANCELED = 4;

    public const TYPE_BUYER = "buyer";
    public const TYPE_SELLER = "seller";

    public const FILTER_STATUS_ALL = 0;
    public const FILTER_STATUS_PENDING = 1;
    public const FILTER_STATUS_PROCESSING = 2;
    public const FILTER_STATUS_SOLVED = 3;
    public const FILTER_STATUS_DECLINED  = 4;
    public const FILTER_STATUS_CANCELED = 5;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    protected $session;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $fileSystem;

    /**
     * @var SellerCollection
     */
    protected $sellerCollection;

    /**
     * @var ProductCollection
     */
    protected $productCollection;

    /**
     * @var OrdersCollection
     */
    protected $ordersCollection;

    /**
     * @var OrderCollection
     */
    protected $orderCollectionFactory;

    /**
     * @var DetailsCollection
     */
    protected $detailsCollection;

    /**
     * @var ReasonsCollection
     */
    protected $reasonsCollection;

    /**
     * @var ConversationCollection
     */
    protected $conversationCollection;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $rma;

    /**
     * @var \Webkul\MpRmaSystem\Model\ReasonsFactory
     */
    protected $reason;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var OrderFactory
     */
    protected $order;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader
     */
    protected $memoLoader;

    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender
     */
    protected $memoSender;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Framework\Locale\CurrencyInterface
     */
    protected $currency;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Sales\Api\CreditmemoManagementInterface
     */
    protected $creditmemoManagement;

    /**
     * @var \Webkul\Marketplace\Helper\Orders
     */
    protected $mpOrdersHelper;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @var \Webkul\Marketplace\Model\Orders
     */
    protected $mpOrder;

    /**
     * @var \Magento\Sales\Model\Order\InvoiceFactory
     */
    protected $invoice;

    /**
     * @var CreditmemoRepositoryInterface
     */
    protected $creditmemoRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var \Magento\Quote\Model\QuoteFactory $quoteFactory
     */
    protected $quoteFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     */
    protected $cartRepositoryInterface;

    /**
     * @var \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     */
    protected $cartManagementInterface;

    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     */
    protected $customerRepository;

    /**
     * @var \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     */
    protected $saleslistFactory;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Session\SessionManager $session
     * @param \Magento\Framework\Filesystem $fileSystem
     * @param SellerCollection $sellerCollection
     * @param ProductCollection $productCollection
     * @param InvoiceItemCollection $invoiceItemCollection
     * @param ShipmentItemCollection $shipmentItemCollection
     * @param OrdersCollection $ordersCollection
     * @param SalesListCollection $salesListCollection
     * @param OrderCollection $orderCollectionFactory
     * @param DetailsCollection $detailsCollectionFactory
     * @param ReasonsCollection $reasonsCollectionFactory
     * @param ConversationCollection $conversationCollectionFactory
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $rma
     * @param \Webkul\MpRmaSystem\Model\ReasonsFactory $reason
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param OrderFactory $orderFactory
     * @param \Webkul\MpRmaSystem\Model\ItemsFactory $items
     * @param \Magento\Framework\Registry $registry
     * @param \Webkul\MpRmaSystem\Model\ResourceModel\Details $detailsResource
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation
     * @param \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader
     * @param \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Framework\Locale\CurrencyInterface $currency
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param DefaultRendererFactory $defaultRenderer
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param StockManagementInterface $stockManagement
     * @param \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Marketplace\Helper\Orders $mpOrdersHelper
     * @param PriceCurrencyInterface $priceCurrency
     * @param \Magento\Directory\Model\CurrencyFactory $currencyFactory
     * @param \Magento\Framework\Module\ResourceInterface $moduleResource
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Webkul\Marketplace\Model\OrdersFactory $mpOrders
     * @param \Magento\Sales\Model\Order\InvoiceFactory $invoice
     * @param CreditmemoRepositoryInterface $creditmemoRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Quote\Model\QuoteFactory $quoteFactory
     * @param \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface
     * @param \Magento\Quote\Api\CartManagementInterface $cartManagementInterface
     * @param \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Session\SessionManager $session,
        \Magento\Framework\Filesystem $fileSystem,
        SellerCollection $sellerCollection,
        ProductCollection $productCollection,
        InvoiceItemCollection $invoiceItemCollection,
        ShipmentItemCollection $shipmentItemCollection,
        OrdersCollection $ordersCollection,
        SalesListCollection $salesListCollection,
        OrderCollection $orderCollectionFactory,
        DetailsCollection $detailsCollectionFactory,
        ReasonsCollection $reasonsCollectionFactory,
        ConversationCollection $conversationCollectionFactory,
        \Webkul\MpRmaSystem\Model\DetailsFactory $rma,
        \Webkul\MpRmaSystem\Model\ReasonsFactory $reason,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        OrderFactory $orderFactory,
        \Webkul\MpRmaSystem\Model\ItemsFactory $items,
        \Magento\Framework\Registry $registry,
        \Webkul\MpRmaSystem\Model\ResourceModel\Details $detailsResource,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Sales\Controller\Adminhtml\Order\CreditmemoLoader $creditmemoLoader,
        \Magento\Sales\Model\Order\Email\Sender\CreditmemoSender $creditmemoSender,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Locale\CurrencyInterface $currency,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Sales\Api\CreditmemoManagementInterface $creditmemoManagement,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        DefaultRendererFactory $defaultRenderer,
        \Magento\Framework\Escaper $escaper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        StockManagementInterface $stockManagement,
        \Magento\Catalog\Model\Indexer\Product\Price\Processor $priceIndexer,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Marketplace\Helper\Orders $mpOrdersHelper,
        PriceCurrencyInterface $priceCurrency,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Module\ResourceInterface $moduleResource,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Marketplace\Model\OrdersFactory $mpOrders,
        \Magento\Sales\Model\Order\InvoiceFactory $invoice,
        CreditmemoRepositoryInterface $creditmemoRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        \Magento\Quote\Api\CartRepositoryInterface $cartRepositoryInterface,
        \Magento\Quote\Api\CartManagementInterface $cartManagementInterface,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory
    ) {
        $this->scopeConfig             = $context->getScopeConfig();
        $this->request                 = $context->getRequest();
        $this->storeManager            = $storeManager;
        $this->customerSession         = $customerSession;
        $this->session                 = $session;
        $this->fileSystem              = $fileSystem;
        $this->sellerCollection        = $sellerCollection;
        $this->productCollection       = $productCollection;
        $this->invoiceItemCollection   = $invoiceItemCollection;
        $this->shipmentItemCollection  = $shipmentItemCollection;
        $this->ordersCollection        = $ordersCollection;
        $this->salesListCollection     = $salesListCollection;
        $this->orderCollectionFactory  = $orderCollectionFactory;
        $this->detailsCollection       = $detailsCollectionFactory;
        $this->reasonsCollection       = $reasonsCollectionFactory;
        $this->conversationCollection  = $conversationCollectionFactory;
        $this->rma                     = $rma;
        $this->reason                  = $reason;
        $this->product                 = $productFactory;
        $this->orderFactory            = $orderFactory;
        $this->items                   = $items;
        $this->registry                = $registry;
        $this->detailsResource         = $detailsResource;
        $this->transportBuilder        = $transportBuilder;
        $this->inlineTranslation       = $inlineTranslation;
        $this->memoLoader              = $creditmemoLoader;
        $this->memoSender              = $creditmemoSender;
        $this->resource                = $resource;
        $this->currency                = $currency;
        $this->customerFactory         = $customerFactory;
        $this->creditmemoManagement    = $creditmemoManagement;
        $this->fileUploader            = $fileUploaderFactory;
        $this->defaultRenderer         = $defaultRenderer;
        $this->escaper                 = $escaper;
        $this->stockRegistry           = $stockRegistry;
        $this->stockManagement         = $stockManagement;
        $this->priceIndexer            = $priceIndexer;
        $this->mpHelper                = $mpHelper;
        $this->mpOrdersHelper          = $mpOrdersHelper;
        $this->priceCurrency           = $priceCurrency;
        $this->currencyFactory         = $currencyFactory;
        $this->moduleResource          = $moduleResource;
        $this->jsonHelper              = $jsonHelper;
        $this->mpOrders                = $mpOrders;
        $this->invoice                 = $invoice;
        $this->creditmemoRepository    = $creditmemoRepository;
        $this->searchCriteriaBuilder   = $searchCriteriaBuilder;
        $this->quoteFactory            = $quoteFactory;
        $this->cartRepositoryInterface = $cartRepositoryInterface;
        $this->cartManagementInterface = $cartManagementInterface;
        $this->customerRepository      = $customerRepository;
        $this->saleslistFactory        = $saleslistFactory;
        parent::__construct($context);
    }

    /**
     * Get Dynamic admin name
     *
     * @return string
     */
    public function getAdminName()
    {
        $path = "trans_email/ident_general/name";
        $scope = ScopeInterface::SCOPE_STORE;
        $admin = $this->scopeConfig->getValue($path, $scope);
        if ($admin) {
            $admin = $admin;
        } else {
            $admin = 'Admin';
        }
        return $admin;
    }

    /**
     * Get Default Days to Request RMA
     *
     * @return int
     */
    public function getDefaultDays()
    {
        $path = "mprmasystem/settings/default_days";
        $scope = ScopeInterface::SCOPE_STORE;
        $days = (int) $this->scopeConfig->getValue($path, $scope);
        if ($days <= 0) {
            $days = 30;
        }

        return $days;
    }

    /**
     * Check Whether Notification to Admin is Allowed or Not
     *
     * @return int
     */
    public function isAllowedNotification()
    {
        $path = "mprmasystem/settings/admin_notification";
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * Get Admin Email Id
     *
     * @return string
     */
    public function getAdminEmail()
    {
        $path = "mprmasystem/settings/admin_email";
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * Get Current Customer Id
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->mpHelper->getCustomerId();
    }

    /**
     * Check Customer is Logged In or Not
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($this->customerSession->isLoggedIn()) {
            return true;
        }

        return false;
    }

    /**
     * Get Mediad Path
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->fileSystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath();
    }

    /**
     * Get Current RMA Id
     *
     * @return int
     */
    public function getCurrentRmaId()
    {
        $id = (int) $this->request->getParam('id');
        return $id;
    }

    /**
     * Get Seller Details
     *
     * @param int $sellerId
     * @return array
     */
    public function getSellerDetails($sellerId)
    {
        $seller = false;
        $collection = $this->sellerCollection
                            ->create()
                            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        foreach ($collection as $seller) {
            return $seller;
        }

        return $seller;
    }

    /**
     * Check Whether Customer Is Seller Or Not
     *
     * @param int $sellerId [optional]
     *
     * @return bool
     */
    public function isSeller($sellerId = '')
    {
        if ($sellerId == '') {
            $sellerId = $this->getSellerId();
        }

        $seller = $this->getSellerDetails($sellerId);
        if ($seller) {
            $isSeller = $seller->getIsSeller();
            if ($isSeller == 1) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Customer's Orders
     *
     * @param int $customerId [optional]
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public function getOrdersOfCustomer($customerId = '')
    {
        $days = $this->getDefaultDays();
        $from = date('Y-m-d', strtotime("-".$days." days"));
        $allowedStatus = ['pending', 'processing', 'complete'];
        if ($customerId == '') {
            $customerId = $this->getCustomerId();
        }

        $orders = $this->orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_id', $customerId)
                        ->addFieldToFilter('status', ['in'=> $allowedStatus])
                        ->addFieldToFilter('created_at', ['from'  => $from])
                        ->setOrder('increment_id', 'desc');

        return $orders;
    }

    /**
     * Get Guest's Orders
     *
     * @param string $email
     * @return \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    public function getOrdersOfGuest($email = '')
    {
        if (strlen($email) <= 0) {
            $email = $this->getGuestEmailId();
        }

        $days = $this->getDefaultDays();
        $from = date('Y-m-d', strtotime("-".$days." days"));
        $allowedStatus = ['pending', 'processing', 'complete'];
        $orders = $this->orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_email', $email)
                        ->addFieldToFilter('status', ['in'=> $allowedStatus])
                        ->addFieldToFilter('created_at', ['from'  => $from])
                        ->setOrder('created_at', 'desc');

        return $orders;
    }

    /**
     * Get Currency Symbol By Currency Code
     *
     * @param string $code Optional
     *
     * @return string
     */
    public function getCurrencySymbol($code = 'USD')
    {
        return $this->currency->getCurrency($code)->getSymbol();
    }

    /**
     * Get Reasons
     *
     * @return array
     */
    public function getAllReasons()
    {
        $reasons = [];
        $collection = $this->reasonsCollection
                            ->create()
                            ->addFieldToFilter('status', 1);
        foreach ($collection as $reason) {
            $reasons[$reason->getId()] = $this->escaper->escapeHtml($reason->getReason());
        }

        return $reasons;
    }

    /**
     * Get Seller Id By Order Id
     *
     * @param int $productId
     * @param int $orderId
     * @return int $sellerId
     */
    public function getSellerIdByOrderId($productId, $orderId)
    {
        $sellerId = 0;
        $collection = $this->salesListCollection
        ->create()
        ->addFieldToFilter('mageproduct_id', $productId)
        ->addFieldToFilter('order_id', $orderId);
        foreach ($collection as $item) {
            $sellerId = $item->getSellerId();
        }

        return $sellerId;
    }

    /**
     * Get Seller Id By Order Item Id
     *
     * @param int $itemId
     *
     * @return int $sellerId
     */
    public function getSellerIdByOrderItemId($itemId)
    {
        $sellerId = null;
        $collection = $this->salesListCollection
                            ->create()
                            ->addFieldToFilter('order_item_id ', $itemId);
        foreach ($collection as $item) {
            $sellerId = $item->getSellerId();
        }

        return $sellerId;
    }

    /**
     * Get Seller Id By Product Id
     *
     * @param int $productId
     *
     * @return int $sellerId
     */
    public function getSellerIdByProductId($productId)
    {
        $sellerId = 0;
        $productCollection = $this->productCollection
                            ->create()
                            ->addFieldToFilter('mageproduct_id', $productId);
        foreach ($productCollection as $product) {
            $sellerId = $product->getSellerId();
        }

        return $sellerId;
    }

    /**
     * Get All Rma of Customer
     *
     * @param int $customerId [optional]
     *
     * @return \Webkul\MpRmaSystem\Model\ResourceModel\Details\Collection
     */
    public function getAllRma($customerId = '')
    {
        if ($customerId == '') {
            $customerId = $this->getCustomerId();
        }

        $collection = $this->detailsCollection
                            ->create()
                            ->addFieldToFilter('customer_id', $customerId);

        return $collection;
    }

    /**
     * Get All Rma of Seller
     *
     * @param int $sellerId
     *
     * @return \Webkul\MpRmaSystem\Model\ResourceModel\Details\Collection
     */
    public function getAllRmaForSeller($sellerId)
    {
        $collection = $this->detailsCollection
                            ->create()
                            ->addFieldToFilter('seller_id', $sellerId);

        return $collection;
    }

    /**
     * Check For Valid RMA For Admin
     *
     * @return bool
     */
    public function isAdminRma()
    {
        $id = $this->getCurrentRmaId();
        $collection = $this->detailsCollection
                            ->create()
                            ->addFieldToFilter('seller_id', 0)
                            ->addFieldToFilter('id', $id);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Check For Valid RMA To View
     *
     * @param int $type [optional]
     *
     * @return bool
     */
    public function isValidRma($type = 0)
    {
        $id = $this->getCurrentRmaId();
        $customerId = $this->getCustomerId();
        $sellerId = $this->getSellerId();
        $email = $this->getGuestEmailId();
        $collection = $this->detailsCollection
                            ->create()
                            ->addFieldToFilter('id', $id);
        if ($type == 1) { // Checking for Customer's Requested RMA
            $collection->addFieldToFilter('customer_id', $customerId);
        } elseif ($type == 2) { // Checking for Guest's Requested RMA
            $collection->addFieldToFilter('customer_email', $email);
        } else { // Checking for Seller's RMA
            $collection->addFieldToFilter('seller_id', $sellerId);
        }

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Get RMA Details by Id
     *
     * @param int $rmaId [optional]
     *
     * @return \Webkul\MpRmaSystem\Model\Details
     */
    public function getRmaDetails($rmaId = 0)
    {
        if ($rmaId == 0) {
            $rmaId = $this->getCurrentRmaId();
        }
        $rma = $this->rma->create()->load($rmaId);
        return $rma;
    }

    /**
     * Get Reason by Id
     *
     * @param int $reasonId
     *
     * @return string
     */
    public function getReasonById($reasonId)
    {
        $reason = $this->reason->create()->load($reasonId);
        if ($reason->getId()) {
            return $this->escaper->escapeHtml($reason->getReason());
        }

        return "";
    }

    /**
     * Get Order Details by Id
     *
     * @param int $orderId
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($orderId)
    {
        $order = $this->orderFactory->create()->load($orderId);
        return $order;
    }

    /**
     * Get Order Item Details by Item Id
     *
     * @param int $orderId
     * @param int $itemId
     *
     * @return Magento\Sales\Model\Order\Item
     */
    public function getOrderItem($orderId, $itemId)
    {
        $order = $this->getOrder($orderId);
        $orderedItems = $order->getAllVisibleItems();
        foreach ($orderedItems as $item) {
            if ($item->getId() == $itemId) {
                return $item;
            }
        }

        return "";
    }

    /**
     * Get Images by Rma Id
     *
     * @param int $rmaId
     *
     * @return array
     */
    public function getImages($rmaId)
    {
        $currentStore = $this->storeManager->getStore();
        $type = \Magento\Framework\UrlInterface::URL_TYPE_MEDIA;
        $mediaUrl = $currentStore->getBaseUrl($type);
        $imageArray = [];
        $path = $this->getMediaPath()."marketplace/rma/".$rmaId."/*";
        $images = Glob::glob($path);
        foreach ($images as $image) {
            $fileName = explode("/", $image);
            $fileName = end($fileName);
            $imageUrl = $mediaUrl.'marketplace/rma/'.$rmaId."/".$fileName;
            $imageArray[] = $imageUrl;
        }

        return $imageArray;
    }

    /**
     * Get Conversation on Rma by RMA Id
     *
     * @param int $rmaId
     *
     * @return \Webkul\MpRmaSystem\Model\ResourceModel\Conversation\Collection
     */
    public function getConversations($rmaId)
    {
        $collection = $this->conversationCollection
                            ->create()
                            ->addFieldToFilter("rma_id", $rmaId)
                            ->setOrder("created_time", "desc");
        return $collection;
    }

    /**
     * Get Customer/Seller Name By RMA Id
     *
     * @param int $rmaId
     * @param boolean $isSeller
     * @return string
     */
    public function getCustomerName($rmaId, $isSeller = true)
    {
        $rma = $this->rma->create()->load($rmaId);
        if ($rma->getId()) {
            $customerId = $rma->getCustomerId();
            if ($isSeller) {
                $customerId = $rma->getSellerId();
            }

            $customer = $this->getCustomer($customerId);
            return $customer->getName();
        }

        return "";
    }

    /**
     * Get Customner by Customer Id
     *
     * @param int $customerId
     *
     * @return \Magento\Customer\Model\Customer
     */
    public function getCustomer($customerId)
    {
        $customer = $this->customerFactory->create()->load($customerId);
        return $customer;
    }

    /**
     * Get Seller Status Title
     *
     * @param int $status
     *
     * @return string
     */
    public function getSellerStatusTitle($status)
    {
        if ($status == self::SELLER_STATUS_PENDING || $status == self::SELLER_STATUS_PACKAGE_NOT_RECEIVED) {
            $sellerStatus = "Pending";
        } elseif ($status == self::SELLER_STATUS_PACKAGE_RECEIVED) {
            $sellerStatus = "Processing";
        } elseif ($status == self::SELLER_STATUS_PACKAGE_DISPATCHED) {
            $sellerStatus = "Solved";
        } elseif ($status == self::SELLER_STATUS_SOLVED) {
            $sellerStatus = "Solved";
        } elseif ($status == self::SELLER_STATUS_DECLINED) {
            $sellerStatus = "Declined";
        } else {
            $sellerStatus = "Authorized";
        }

        return $sellerStatus;
    }

    /**
     * Get Resolution Type Title
     *
     * @param int $status
     *
     * @return string
     */
    public function getResolutionTypeTitle($status)
    {
        if ($status == self::RESOLUTION_REFUND) {
            $resolution = self::RESOLUTION_REFUND_LABEL;
        } elseif ($status == self::RESOLUTION_REPLACE) {
            $resolution = self::RESOLUTION_REPLACE_LABEL;
        } else {
            $resolution = self::RESOLUTION_CANCEL_LABEL;
        }

        $resolution = __($resolution);
        return $resolution;
    }

    /**
     * Get Order Status Title
     *
     * @param int $status
     *
     * @return string
     */
    public function getOrderStatusTitle($status)
    {
        if ($status == self::ORDER_DELIVERED) {
            $orderStatus = self::ORDER_DELIVERED_LABEL;
        } elseif ($status == self::ORDER_NOT_DELIVERED) {
            $orderStatus = self::ORDER_NOT_DELIVERED_LABEL;
        } else {
            $orderStatus = self::ORDER_NOT_APPLICABLE_LABEL;
        }

        $orderStatus = __($orderStatus);
        return $orderStatus;
    }

    /**
     * Get RMA Status Title
     *
     * @param int $status
     * @param int $finalStatus
     *
     * @return string
     */
    public function getRmaStatusTitle($status, $finalStatus = 0)
    {
       
        if ($finalStatus == self::FINAL_STATUS_PENDING) {
            if ($status == self::RMA_STATUS_PROCESSING) {
                $rmaStatus = __("Processing");
            } elseif ($status == self::RMA_STATUS_SOLVED) {
                $rmaStatus = __("Solved");
            } elseif ($status == self::RMA_STATUS_DECLINED) {
                $rmaStatus = __("Declined");
            } elseif ($status == self::RMA_STATUS_CANCELED) {
                $rmaStatus = __("Canceled");
            } else {
                $rmaStatus = __("Pending");
            }
        } else {
            if ($finalStatus == self::FINAL_STATUS_CANCELED) {
                $rmaStatus = __("Canceled");
            } elseif ($finalStatus == self::FINAL_STATUS_DECLINED) {
                $rmaStatus = __("Declined");
            } elseif ($finalStatus == self::FINAL_STATUS_SOLVED || $finalStatus == self::FINAL_STATUS_CLOSED) {
                $rmaStatus = __("Solved");
            } else {
                $rmaStatus = __("Pending");
            }
        }

        return $rmaStatus;
    }

    /**
     * Get Seller's All Status
     *
     * @param string $resolutionType
     * @param string $productType
     * @param integer $orderStatus
     * @return string
     */
    public function getAllStatus($resolutionType, $productType, $orderStatus = 1)
    {
        if ($resolutionType == self::RESOLUTION_CANCEL) {
            $allStatus = [
                            self::SELLER_STATUS_PENDING => __('Pending'),
                            self::SELLER_STATUS_DECLINED => __('Declined'),
                            self::SELLER_STATUS_ITEM_CANCELED => __('Authorized')
                        ];
        } else {
            if ($orderStatus == self::ORDER_DELIVERED) {
                if ($productType == "intangible") {
                    $allStatus = [
                        self::SELLER_STATUS_PENDING => __('Pending'),
                        self::SELLER_STATUS_DECLINED => __('Declined')
                    ];
                } else {
                    if ($resolutionType == self::RESOLUTION_REFUND) {
                        $allStatus = [
                            self::SELLER_STATUS_PENDING => __('Pending'),
                            self::SELLER_STATUS_PACKAGE_NOT_RECEIVED => __('Not Receive Package yet'),
                            self::SELLER_STATUS_PACKAGE_RECEIVED => __('Received Package'),
                            self::SELLER_STATUS_DECLINED => __('Declined')
                        ];
                    } else {
                        $allStatus = [
                            self::SELLER_STATUS_PENDING => __('Pending'),
                            self::SELLER_STATUS_PACKAGE_NOT_RECEIVED => __('Not Receive Package yet'),
                            self::SELLER_STATUS_PACKAGE_RECEIVED => __('Received Package'),
                            self::SELLER_STATUS_PACKAGE_DISPATCHED => __('Dispatched Package'),
                            self::SELLER_STATUS_DECLINED => __('Declined')
                        ];
                    }
                }
            } elseif ($orderStatus == self::ORDER_NOT_DELIVERED) {
                $allStatus = [
                            self::SELLER_STATUS_PENDING => __('Pending'),
                            self::SELLER_STATUS_DECLINED => __('Declined')
                    ];
            } else {
                $allStatus = [
                            self::SELLER_STATUS_PENDING => __('Pending'),
                            self::SELLER_STATUS_DECLINED => __('Declined')
                    ];
            }
        }

        return $allStatus;
    }

    /**
     * Create Creditmemo
     *
     * @param array $data
     *
     * @return array
     */
    public function createCreditMemo($data)
    {
        $error = 0;
        $result = ['msg' => '', 'error' => ''];
        $rmaId = $data['rma_id'];
        $negative = $data['negative'];
        $rma = $this->getRmaDetails($rmaId);
        $stock = $data['back_to_stock'];
        $orderId = $rma->getOrderId();
        $productDetails = $this->getRmaProductDetails($rmaId);
        $items = [];
        foreach ($productDetails as $product) {
            if ($stock) {
                $items[$product->getItemId()] = ['qty' => $product->getQty(),'back_to_stock'=>$stock];
            } else {
                $items[$product->getItemId()] = ['qty' => $product->getQty()];
            }
        }

        $memoData = [
                    'items' => $items,
                    'do_offline' => (int)$data['do_offline'],
                    'comment_text' => "",
                    'shipping_amount' => 0,
                    'adjustment_positive' => 0,
                    'adjustment_negative' => $negative
                ];

        try {
            $this->memoLoader->setOrderId($orderId);
            $this->memoLoader->setCreditmemoId("");
            $this->memoLoader->setCreditmemo($memoData);
            $this->memoLoader->setInvoiceId($data['invoice_id']);
            $memo = $this->memoLoader->load();
            if ($memo) {
                if (!$memo->isValidGrandTotal()) {
                    $result['msg'] = __('Total must be positive.');
                    $result['error'] = 1;
                    return $result;
                }

                if (!empty($memo['comment_text'])) {
                    $memo->addComment(
                        $memo['comment_text'],
                        isset($memo['comment_customer_notify']),
                        isset($memo['is_visible_on_front'])
                    );

                    $memo->setCustomerNote($memo['comment_text']);
                    $memo->setCustomerNoteNotify(isset($memo['comment_customer_notify']));
                }

                if (isset($memo['do_offline'])) {
                    //do not allow online refund for Refund to Store Credit
                    if (!$memo['do_offline'] && !empty($memo['refund_customerbalance_return_enable'])) {
                        $result['msg'] = __('Cannot create online refund.');
                        $result['error'] = 1;
                        return $result;
                    }
                }

                $memoManagement = $this->creditmemoManagement;
                $memoManagement->refund($memo, (bool)$memo['do_offline'], !empty($memo['send_email']));

                if (!empty($memo['send_email'])) {
                    $this->memoSender->send($memo);
                }

                $result['msg'] = __('Credit memo generated successfully.');
                $result['error'] = 0;
                $result['memo_id'] = $memo->getId();
                return $result;
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $result['msg'] = $e->getMessage();
            $result['error'] = 1;
        } catch (\Exception $e) {
            $result['msg'] = __('Unable to save credit memo right now.');
            $result['error'] = 1;
        }

        return $result;
    }
    
    /**
     * Get Close RMA Text
     *
     * @param int $status
     * @param int $sellerName
     * @return string
     */
    public function getCloseRmaLabel($status, $sellerName)
    {
        $label = "";
        if ($status == 1) {
            $label = __("RMA is canceled by Customer.");
        } elseif (trim($sellerName) == null && $status == 2) {
            $label = __("RMA is declined by Admin.");
        } elseif ($status == 2) {
            $label = __("RMA is declined by Seller.");
        } elseif ($status == 3 || $status == 4) {
            $label = __("RMA is Solved.");
        }

        return $label;
    }

    /**
     * Get Product Details of Requested RMA
     *
     * @param int $rmaId
     *
     * @return array
     */
    public function getRmaProductDetails($rmaId)
    {
        return $this->detailsResource->getRmaProductDetails($rmaId);
    }

    /**
     * Get Requested Quantity of RMA by Order Item Id
     *
     * @param int $itemId
     * @param int $orderId
     *
     * @return int
     */
    public function getItemRmaQty($itemId, $orderId)
    {
        $totalQty = 0;
        $collection = $this->detailsCollection->create();
        $tableName = $this->resource->getTableName('marketplace_rma_items');
        $sql = "main_table.id = rma_items.rma_id ";
        $collection->getSelect()->join(['rma_items' => $tableName], $sql, ['*']);
        $collection->addFilterToMap('item_id', 'rma_items.item_id');
        $condition = "(";
        $condition .= "(rma_items.item_id = $itemId)";
        $condition .= " AND (order_id = $orderId)";
        $condition .= " AND (final_status = 0)";
        $condition .= ")";
        $condition .= " OR ";
        $condition .= " (";
        $condition .= " (rma_items.is_qty_returned = 1)";
        $condition .= " AND (rma_items.item_id = $itemId)";
        $condition .= ")";
        $collection->getSelect()->where($condition);
        $collection->getSelect()->group("main_table.id");
        foreach ($collection as $item) {
            $totalQty += $item->getQty();
        }

        return $totalQty;
    }

    /**
     * Get Final Quantity Which Can Be Requested
     *
     * @param int $itemId
     * @param int $orderId
     * @param int $qty
     * @param int $type
     *
     * @return int
     */
    public function getRmaQty($itemId, $orderId, $qty, $type)
    {
        if ($type == 1) {
            $qty = 0;
            $collection = $this->invoiceItemCollection
                                ->create()
                                ->addFieldToFilter('order_item_id', $itemId);
            foreach ($collection as $item) {
                $qty += $item->getQty();
            }
        }

        $rmaQty = $this->getItemRmaQty($itemId, $orderId);
        $qty = $qty - $rmaQty;
        return $qty;
    }

    /**
     * Check Whether RMA is Allowed for Quantity or Not
     *
     * @param int $itemId
     * @param int $orderId
     * @param int $qty
     *
     * @return bool
     */
    public function isRmaAllowed($itemId, $orderId, $qty)
    {
        $rmaQty = $this->getItemRmaQty($itemId, $orderId);
        $orderItem = $this->getOrderItem($orderId, $itemId);
        $totalQty = $orderItem->getQtyOrdered();
        $allowedQty = $totalQty - $rmaQty;
        if ($qty > $allowedQty) {
            return false;
        }

        return true;
    }

    /**
     * Send New RMA Email
     *
     * @param array $details
     * @return void
     */
    public function sendNewRmaEmail($details = [])
    {
        $details['template'] = self::NEW_RMA;
        $orderStatus = $this->getOrderStatusTitle($details['rma']['order_status']);
        $resolutionType = $this->getResolutionTypeTitle($details['rma']['resolution_type']);
        $additionalInfo = $details['rma']['additional_info'];
        $customerName = $details['name'];
        $templateVars = [
                            'name' => $customerName,
                            'rma_id' => $details['rma']['rma_id'],
                            'order_id' => $details['rma']['order_ref'],
                            'order_status' => $orderStatus->__toString(),
                            'resolution_type' => $resolutionType->__toString(),
                            'additional_info' => $additionalInfo
                        ];

        $details['email'] = $details['rma']['customer_email'];
        if ($details['rma']['customer_id'] > 0) {
            $msg = __("New RMA is requested by customer.");
        } else {
            $msg = __("New RMA is requested by guest.");
        }

        //send to seller
        $sellerId = $details['rma']['seller_id'];
        if ($sellerId > 0) {
            $seller = $this->getCustomer($sellerId);
            $email = $seller->getEmail();
            $sellerName = $seller->getName();
            $templateVars['msg'] = $msg->__toString();
            $templateVars['name'] = $sellerName;
            $details['email'] = $email;
            $details['template_vars'] = $templateVars;
            $this->sendEmail($details);
        }
        
        //send to admin
        if ($this->isAllowedNotification()) {
            $adminEmail = $this->getAdminEmail();
            $templateVars['msg'] = $msg->__toString();
            $templateVars['name'] = $this->getAdminName();
            $details['template_vars'] = $templateVars;
            $details['email'] = $adminEmail;
            $this->sendEmail($details);
        }

        //send to customer/guest
        $msg = __("You requested new RMA.");
        $templateVars['msg'] = $msg->__toString();
        $templateVars['name'] = $customerName;

        $details['template_vars'] = $templateVars;
        $details['email'] = $details['rma']['customer_email'];
        $this->sendEmail($details);
    }

    /**
     * Send Update RMA Email
     *
     * @param array $details
     * @return void
     */
    public function sendUpdateRmaEmail($details = [])
    {
        $details['template'] = self::UPDATE_RMA;
        $rma = $this->getRmaDetails($details['rma_id']);
        $finalStatus = $rma->getFinalStatus();
        $sellerStatus = $rma->getSellerStatus();
        $status = $rma->getStatus();
        $rmaStatusTitle = $this->getRmaStatusTitle($status, $finalStatus);
        $sellerStatusTitle = $this->getSellerStatusTitle($sellerStatus);
        $sellerId = $rma->getSellerId();
        $customerId = $rma->getCustomerId();
        $email = $rma->getCustomerEmail();
        $customerEmail = $rma->getCustomerEmail();
        $adminEmail = $this->getAdminEmail();
        $adminName = $this->getAdminName();
        if ($customerId > 0) {
            $customer = $this->getCustomer($customerId);
            $customerName = $customer->getName();
        } else {
            $customerName = 'Guest';
        }

        $templateVars = [
                            'name' => $customerName,
                            'rma_id' => $details['rma_id'],
                            'rma_status' => $rmaStatusTitle->__toString(),
                            'seller_status' => $sellerStatusTitle
                        ];
        //send to customer/guest
        $msg = __("RMA status is updated.");
        $templateVars['msg'] = $msg->__toString();
        $details['template_vars'] = $templateVars;
        $details['email'] = $customerEmail;
        $this->sendEmail($details);
        //send to seller
        $msg = __("RMA status is updated.");
        $templateVars['msg'] = $msg->__toString();
        if ($sellerId == 0) {
            $email = $this->mpHelper->getAdminEmailId();
            $sellerName = $this->mpHelper->getAdminName();
        } else {
            $seller = $this->getCustomer($sellerId);
            $email = $seller->getEmail();
            $sellerName = $seller->getName();
        }
        
        $templateVars['name'] = $sellerName;
        $details['email'] = $email;
        $details['template_vars'] = $templateVars;
        $this->sendEmail($details);
        //send to admin
        $templateVars['name'] = $this->getAdminName();
        $details['template_vars'] = $templateVars;
        $this->sendNotificationToAdmin($details);
    }

    /**
     * Send New Message Email
     *
     * @param array $details
     * @return void
     */
    public function sendNewMessageEmail($details)
    {
        $isAdmin = false;
        $details['template'] = self::RMA_MESSAGE;
        $rmaId = $details['rma_id'];
        $message = $details['message'];
        $senderType = $details['sender_type'];
        $rma = $this->getRmaDetails($rmaId);
        $sellerId = $rma->getSellerId();
        $seller = $this->getCustomer($rma->getSellerId());
        $customerId = $rma->getCustomerId();
        $customerEmail = $rma->getCustomerEmail();
        $adminEmail = $this->getAdminEmail();
        $adminName = $this->getAdminName();
        $customerName = $this->getSenderName($customerId);
        $senderData = $this->getSenderDetails($sellerId);

        if ($senderType == 1) { // seller send message
            $msg = __("Seller sent message on RMA #%1", $rmaId);
            $senderData = [
                            'email' => $customerEmail,
                            'name' => $customerName
                        ];
        } elseif ($senderType == 2) { // Customer send message
            $msg = __("Customer sent message on RMA #%1", $rmaId);
        } elseif ($senderType == 3) { // Guest send message
            $msg = __("Guest sent message on RMA #%1", $rmaId);
        } else { // Admin send message
            $isAdmin = true;
            $msg = __("Admin sent message on RMA #%1", $rmaId);
        }

        $details['email'] = $senderData['email'];
        $templateVars = [
                        'name' => $senderData['name'],
                        'message' => $message,
                        'msg' => $msg
                    ];
        $details['template_vars'] = $templateVars;
        $this->sendEmail($details);
        if ($isAdmin) {
            //Send to Customer
            $details['email'] = $customerEmail;
            $details['template_vars']['name'] = $customerName;
        } else {
            //Send to Admin
            $this->sendNotificationToAdmin($details);
        }
    }

    /**
     * Send Email
     *
     * @param array $details
     * @return void
     */
    public function sendEmail($details)
    {
        try {
            $adminEmail = $this->getAdminEmail();
            $adminName = $this->getAdminName();
            $area = \Magento\Framework\App\Area::AREA_FRONTEND;
            $storeId = $this->storeManager->getStore()->getId();
            $sender = ['name' => $adminName, 'email' => $adminEmail];
            $templateId = $this->getTemplateId('mprmasystem/email/'.$details['template']);
            $transport = $this->transportBuilder
                            ->setTemplateIdentifier($templateId)
                            ->setTemplateOptions(['area' => $area, 'store' => $storeId])
                            ->setTemplateVars($details['template_vars'])
                            ->setFrom($sender)
                            ->addTo($details['email'])
                            ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            return;
        } catch (\Exception $e) {
            $this->inlineTranslation->resume();
        }
    }

    /**
     * Get template
     *
     * @param string $xmlPath
     * @return string
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Get config data
     *
     * @param string $path
     * @param int $storeId
     * @return bool
     */
    protected function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get store
     *
     * @return \Magento\Store\Model\StoreManagerInterface
     */
    public function getStore()
    {
        return $this->storeManager->getStore();
    }

    /**
     * Check Guest Details
     *
     * @param int $incrementId
     * @param string $email
     *
     * @return bool
     */
    public function authenticate($incrementId, $email)
    {
        $orders = $this->orderCollectionFactory->create()
                        ->addFieldToSelect('*')
                        ->addFieldToFilter('customer_email', $email)
                        ->addFieldToFilter('increment_id', $incrementId)
                        ->addFieldToFilter('customer_is_guest', 1)
                        ->setPageSize(1);
        if ($orders->getSize()) {
            return true;
        }

        return false;
    }

    /**
     * Login Guest
     *
     * @param string $email
     */
    public function loginGuest($email)
    {
        $this->session->setGuestEmailId($email);
    }

    /**
     * Check Whether Guest is Logged In or Not
     *
     * @return bool
     */
    public function isGuestLoggedIn()
    {
        if (!empty($this->session->getGuestEmailId())) {
            $email = trim($this->session->getGuestEmailId());
            if ($email != "") {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Guest Email Id
     *
     * @return bool
     */
    public function getGuestEmailId()
    {
        if (!empty($this->session->getGuestEmailId())) {
            return trim($this->session->getGuestEmailId());
        }
    }

    /**
     * Get List of Jquery Errors
     *
     * @return array
     */
    public function getJsErrorList()
    {
        $errors = [];
        $errors[] = "There is some error in preview image.";
        $errors[] = "Quantity not allowed for RMA.";
        $errors[] = "Please select item.";
        $errors[] = "Please select quantity.";
        $errors[] = "Image type not allowed.";
        return $errors;
    }

    /**
     * Validate Image
     *
     * @param string $imagePath
     *
     * @return bool
     */
    public function validateImage($imagePath)
    {
        try {
            $content = $this->fileSystem->getDirectoryWrite(DirectoryList::MEDIA)->getDriver()
            ->fileGetContents($imagePath);
            $success = true;
        } catch (\Exception $e) {
            $success = false;
        }

        return $success;
    }

    /**
     * Check Whether Image Upload Allowed or Not
     *
     * @param int $numberOfImages
     *
     * @return bool
     */
    public function isAllowedImageUpload($numberOfImages)
    {
        $success = true;
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        if ($numberOfImages > 0) {
            for ($i = 0; $i < $numberOfImages; $i++) {
                $fileId = "showcase[$i]";
                try {
                    $uploader = $this->fileUploader->create(['fileId' => $fileId]);
                    $uploader->setAllowedExtensions($allowedExtensions);
                    $imageData = $uploader->validateFile();
                    $isValidImage = $this->validateImage($imageData['tmp_name']);
                    if (!$isValidImage) {
                        $success =  false;
                        break;
                    }
                } catch (\Exception $e) {
                    $success =  false;
                    break;
                }
            }
        }

        return $success;
    }

    /**
     * Get Skipped Images Indexes
     *
     * @return array
     */
    public function getSkippedImagesIndex()
    {
        $skippedIndexs = $this->request->getParam('skip_checked');
        if (!empty($skippedIndexs) && $skippedIndexs != null) {
            $skippedIndexs = trim($skippedIndexs);
            if (strpos($skippedIndexs, ",") !== false) {
                $skippedIndexs = explode(",", $skippedIndexs);
            }
        } else {
            if ($skippedIndexs == "") {
                $skippedIndexs = [];
            } else {
                $skippedIndexs = [$skippedIndexs];
            }
        }

        return $skippedIndexs;
    }

    /**
     * Upload All Images of Rma
     *
     * @param int $numberOfImages
     * @param int $id
     * @return void
     */
    public function uploadImages($numberOfImages, $id)
    {
        if ($numberOfImages > 0) {
            $skippedIndexs = $this->getSkippedImagesIndex();
            $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
            $uploadPath = $this->fileSystem
                                ->getDirectoryRead(DirectoryList::MEDIA)
                                ->getAbsolutePath('marketplace/rma/');
            $uploadPath .= $id;
            $count = 0;
            for ($i = 0; $i < $numberOfImages; $i++) {
                $fileId = "showcase[$i]";
                ++$count;
                $this->uploadImage($fileId, $uploadPath, $count);
            }
        }
    }

    /**
     * Upload Image of Rma
     *
     * @param string $fileId
     * @param string $uploadPath
     * @param int $count
     * @return void
     */
    public function uploadImage($fileId, $uploadPath, $count)
    {
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        try {
            $uploader = $this->fileUploader->create(['fileId' => $fileId]);
            $uploader->setAllowedExtensions($allowedExtensions);
            $imageData = $uploader->validateFile();
            $name = $imageData['name'];
            $ext = explode('.', $name);
            $ext = strtolower(end($ext));
            $imageName = 'image'.$count.'.'.$ext;
            $uploader->setAllowRenameFiles(true);
            $uploader->setFilesDispersion(false);
            $uploader->save($uploadPath, $imageName);
        } catch (\Exception $e) {
            $error =  true;
        }
    }

    /**
     * Upload Image of Rma
     *
     * @param int $customerId
     *
     * @return string
     */
    public function getSenderName($customerId)
    {
        if ($customerId > 0) {
            $customer = $this->getCustomer($customerId);
            $customerName = $customer->getName();
        } else {
            $customerName = __('Guest');
        }

        return $customerName;
    }

    /**
     * Send Notification to Admin
     *
     * @param array $details
     * @return void
     */
    public function sendNotificationToAdmin($details)
    {
        $adminEmail = $this->getAdminEmail();
        $adminName = $this->getAdminName();
        if ($this->isAllowedNotification()) {
            $details['email'] = $adminEmail;
            $details['template_vars']['name'] = $adminName;
            $this->sendEmail($details);
        }
    }

    /**
     * Get Sender Details
     *
     * @param int $sellerId
     * @return array
     */
    public function getSenderDetails($sellerId)
    {
        $result = [];
        if ($sellerId > 0) {
            $seller = $this->getCustomer($sellerId);
            $result['email'] = $seller->getEmail();
            $result['name'] = $seller->getName();
        } else {
            $result['email'] = $this->getAdminEmail();
            $result['name'] = $this->getAdminName();
        }

        return $result;
    }

    /**
     * Get Rma Reason Lable
     *
     * @return string
     */
    public function getRmaResonsLabel()
    {
        $reasonLables = [];
        $rmaId = $this->getCurrentRmaId();
        $collection = $this->items
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter("rma_id", $rmaId);
        foreach ($collection as $item) {
            $reasonLables[] = $this->getReasonById($item->getReasonId());
        }

        return implode(", ", $reasonLables);
    }

    /**
     * Set Rma Items
     *
     * @param array $productIds
     * @param array $allReasons
     * @param array $allQtys
     * @param array $allPrices
     * @param int $rmaId
     *
     * @return array
     */
    public function setItemsData($productIds, $allReasons, $allQtys, $allPrices, $rmaId)
    {
        try {
            foreach ($productIds as $itemId => $productId) {
                $data = [];
                $data['rma_id'] = $rmaId;
                $data['item_id'] = $itemId;
                $data['product_id'] = $productId;
                $data['qty'] = $allQtys[$itemId];
                $data['price'] = $allPrices[$itemId];
                $data['reason_id'] = $allReasons[$itemId];
                $this->saveItemData($data);
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Save Item data
     *
     * @param array $data
     */
    public function saveItemData($data)
    {
        $this->items->create()->setData($data)->save();
    }

    /**
     * Get Seller Details By Product Id
     *
     * @param int $productId
     *
     * @return array
     */
    public function getSellerDetailsByProductId($productId)
    {
        $details = ["seller_id" => 0, "seller_name" => "Admin"];
        $collection = $this->productCollection
                            ->create()
                            ->addFieldToFilter('mageproduct_id', $productId);
        foreach ($collection as $order) {
            $sellerId = $order->getSellerId();
            $seller = $this->getSellerDetails($sellerId);
            if ($seller) {
                $details = ["seller_id" => $sellerId, "seller_name" => $seller->getShopUrl()];
            }
        }

        return $details;
    }

    /**
     * Get Params
     *
     * @return array
     */
    public function getParams()
    {
        return $this->request->getParams();
    }

    /**
     * Get All Rma Items For Email Template
     *
     * @return collection
     */
    public function getAllItems()
    {
        $rmaId = $this->getRegistry("rma_id");
        return $this->getRmaProductDetails($rmaId);
    }

    /**
     * Set Data In Registry
     *
     * @param string $key
     * @param mix $value
     *
     * @return array
     */
    public function setRegistry($key, $value)
    {
        $this->registry->register($key, $value);
    }

    /**
     * Get Data In Registry
     *
     * @param string $key
     *
     * @return array
     */
    public function getRegistry($key)
    {
        return $this->registry->registry($key);
    }

    /**
     * Get Order Item Option Html
     *
     * @param object $orderItem
     *
     * @return html
     */
    public function getOptionsHtml($orderItem)
    {
        $html = "";
        $block = $this->defaultRenderer->create();
        $block->setItem($orderItem);
        if ($_options = $block->getItemOptions()) {
            $html .= "<dl class='item-options'>";
            foreach ($_options as $_option) {
                $html .= "<dt>";
                $html .= $block->escapeHtml($_option['label']);
                $html .= "</dt>";
                if (!$block->getPrintStatus()) {
                    $_formatedOptionValue = $block->getFormatedOptionValue($_option);
                    $html .= "<dd>";
                    if (isset($_formatedOptionValue['full_view'])) {
                        $html .= $_formatedOptionValue['full_view'];
                    } else {
                        $html .= $_formatedOptionValue['value'];
                    }

                    $html .= "</dd>";
                } else {
                    if (isset($_option['print_value'])) {
                        $label = $_option['print_value'];
                    } else {
                        $label = $_option['value'];
                    }

                    $html .= "<dd>";
                    $html .= nl2br($block->escapeHtml($label));
                    $html .= "</dd>";
                }
            }

            $html .= "</dl>";
        }

        $addtInfoBlock = $block->getProductAdditionalInformationBlock();
        if ($addtInfoBlock) {
            $html .= $addtInfoBlock->setItem($orderItem)->toHtml();
        }

        return $html;
    }

    /**
     * Get Valid Price Of Item For RMA Refund
     *
     * @param Object $item
     *
     * @return float
     */
    public function getItemFinalPrice($item)
    {
        $sellerId = $this->getSellerId();
        $orderId = $item->getOrderId();
        $sellerOrder = $this->mpOrders->create()->getCollection()
                        ->addFieldToFilter('seller_id', ['in' => $sellerId])
                        ->addFieldToFilter('order_id', $orderId);
        $sellerOrder->getSelect()->limit(1);
        $shippingAmount = 0;
        if ($sellerOrder->getSize()) {
            foreach ($sellerOrder as $mpOrder) {
                $shippingCharge = $mpOrder->getShippingCharges();
                $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
                $shippingAmount = $this->convertPriceFromBase($shippingCharge, $currentCurrencyCode);
            }
        }
        $rmaQty = $item->getQty();
        $qty = $item->getQtyOrdered();
        $totalPrice = $item->getRowTotal();
        $discountAmount = $item->getDiscountAmount();
        $taxAmount = $item->getTaxAmount();
        $finalPrice = $totalPrice + $taxAmount + $shippingAmount - $discountAmount;
        $unitPrice = $finalPrice / $qty;
        $finalPrice = $unitPrice * $rmaQty;
        return $finalPrice;
    }

    /**
     * Get status Details
     *
     * @param array $orderDetails
     * @return array
     */
    public function getStatusDetails($orderDetails)
    {
        $result = [];
        foreach ($orderDetails as $sellerId => $items) {
            $collection = $this->invoiceItemCollection
                                ->create()
                                ->addFieldToFilter("order_item_id", ["in" => $items]);
            if ($collection->getSize()) {
                $result[$sellerId]['order_status'] = self::ORDER_DELIVERED;
            } else {
                $result[$sellerId]['order_status'] = self::ORDER_NOT_DELIVERED;
            }

            $collection = $this->shipmentItemCollection
                                ->create()
                                ->addFieldToFilter("order_item_id", ["in" => $items]);
            if ($collection->getSize()) {
                $result[$sellerId]['shipment_status'] = 1;
            } else {
                $result[$sellerId]['shipment_status'] = 0;
            }
        }

        return $result;
    }

    /**
     * Cancel Order Item
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $qty
     * @return void
     */
    public function cancelOrderItem($item, $qty)
    {
        if ($item->getStatusId() !== OrderItem::STATUS_CANCELED) {
            $totalCanceledQty = $qty + $item->getQtyCanceled();
            $item->setQtyCanceled($totalCanceledQty);
            $item->setTaxCanceled(
                $item->getTaxCanceled() + $item->getBaseTaxAmount() * $item->getQtyCanceled() / $item->getQtyOrdered()
            );
            $item->setDiscountTaxCompensationCanceled(
                $item->getDiscountTaxCompensationCanceled() +
                $item->getDiscountTaxCompensationAmount() * $item->getQtyCanceled() / $item->getQtyOrdered()
            );
            $item->save();
            $this->returnItemStock($item, $qty);
            $this->priceIndexer->reindexRow($item->getProductId());
        }
    }

    /**
     * Return Product Quantity To Stock
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int $qty
     * @return void
     */
    public function returnItemStock($item, $qty)
    {
        $children = $item->getChildrenItems();
        $productId = $item->getProductId();
        $websiteId = $item->getStore()->getWebsiteId();
        if ($item->getId() && $productId && empty($children) && $qty) {
            $this->stockManagement->backItemQty($productId, $qty, $websiteId);
        }
    }

    /**
     * Manage Stock By RMA Id
     *
     * @param int $rmaId
     * @param boolean $productDetails
     * @return void
     */
    public function manageStock($rmaId, $productDetails = false)
    {
        if (!$productDetails) {
            $productDetails = $this->getRmaProductDetails($rmaId);
        }

        foreach ($productDetails as $item) {
            $qty = $item->getQty();
            $this->returnItemStock($item, $qty);
        }
    }

    /**
     * Process RMA Cancellation
     *
     * @param int $rmaId
     * @return void
     */
    public function processCancellation($rmaId)
    {
        $productDetails = $this->getRmaProductDetails($rmaId);
        $qtyOrdered = 0;
        $cancledQty = 0;
        foreach ($productDetails as $item) {
            $qty = $item->getQty();
            $totalCanceledQty = $qty + $item->getQtyCanceled();
            $cancledQty = $cancledQty + $totalCanceledQty;
            $qtyOrdered = $qtyOrdered + $item->getQtyOrdered();
            $orderId = $item->getOrderId();
            $this->cancelOrderItem($item, $qty);
            
        }
        if ($qtyOrdered == $cancledQty) {
            $this->setOrderStatus($orderId, $rmaId);
            $this->cancelOrder($orderId);
        }
    }

    /**
     * Cancelled Order
     *
     * @param int $orderId
     * @return void
     */
    public function cancelOrder($orderId)
    {
        $order = $this->getOrder($orderId);

        if ($order->canCancel()) {
            $order->getPayment()->cancel();
            $order->cancel()->save();
        }
    }

    /**
     * Set Order Status
     *
     * @param int $ordId
     * @param int $rmaId
     * @return void
     */
    public function setOrderStatus($ordId, $rmaId)
    {
        $rma = $this->getRmaDetails($rmaId);
        $sellerId = $rma->getSellerId();
        $marketplaceOrderCollection = $this->mpOrders->create()->getCollection()
                                    ->addFieldToFilter('order_id', $ordId)
                                    ->addFieldToFilter('seller_id', $sellerId);
        if ($marketplaceOrderCollection->getSize()) {
            foreach ($marketplaceOrderCollection as $mpOrderData) {
                $mpOrderData->setTrackingNumber('canceled');
                $mpOrderData->setCarrierName('canceled');
                $mpOrderData->setIsCanceled(1);
                $mpOrderData->setOrderStatus('canceled');
                $mpOrderData->save();
            }
        }
    }

    /**
     * Update RMA Item Qunatity Status
     *
     * @param int $rmaId
     * @return void
     */
    public function updateRmaItemQtyStatus($rmaId)
    {
        $collection = $this->items
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter("rma_id", $rmaId);
        foreach ($collection as $item) {
            $this->setStatus($item, 1);
        }
    }

    /**
     * Change is qty returned Flag
     *
     * @param collection $item
     * @param int $status
     * @return void
     */
    public function setStatus($item, $status)
    {
        $item->addData(['is_qty_returned' => $status])
                ->setId($item->getId())
                ->save();
    }

    /**
     * Get Current Seller Id
     *
     * @return int
     */
    public function getSellerId()
    {
        return $this->getCustomerId();
    }

    /**
     * Update MpOrder
     *
     * @param int $orderId
     * @param int $memoId
     * @return void
     */
    public function updateMpOrder($orderId, $memoId)
    {
        $sellerId = $this->getSellerId();
        $collection = $this->ordersCollection
                            ->create()
                            ->addFieldToFilter('seller_id', $sellerId)
                            ->addFieldToFilter('order_id', $orderId);
        $collection->getSelect()->limit(1);
        if ($collection->getSize()) {
            $creditmemoIds = [];
            foreach ($collection as $mpOrder) {
                $memoIds = $mpOrder->getCreditmemoId();
                if ($memoIds != "") {
                    if (strpos($memoIds, ",") !== false) {
                        $creditmemoIds = explode(',', $memoIds);
                    } else {
                        $creditmemoIds = [$memoIds];
                    }
                }

                array_push($creditmemoIds, $memoId);
            }
        }
    }

    /**
     * Save Credit Memo
     *
     * @param Collection $mpOrder
     * @param string $creditmemoIds
     * @return void
     */
    public function saveCreditMemoId($mpOrder, $creditmemoIds)
    {
        $mpOrder->setCreditmemoId(implode(',', $creditmemoIds));
        $mpOrder->save();
    }

    /**
     * Logout Guest
     *
     * @return void
     */
    public function logoutGuest()
    {
        $this->session->unsGuestEmailId();
    }

    /**
     * Set Filters
     *
     * @param int $key
     * @param mix $value
     * @return void
     */
    public function setFilter($key, $value)
    {
        $this->session->setData($key, $value);
    }

    /**
     * Using for getting Filter using key
     *
     * @param string $key
     * @return void
     */
    public function getFilter($key)
    {
        $this->session->getData($key);
    }

    /**
     * Using for buyer filter by RmaId
     *
     * @return array
     */
    public function getBuyerFilterRmaId()
    {
        return $this->session->getData("buyer_filter_rma_id");
    }

    /**
     * Using for buyer filter by Status
     *
     * @return array
     */
    public function getBuyerFilterStatus()
    {
        return (int) $this->session->getData("buyer_filter_status");
    }

    /**
     * Using for buyer filter by Order Ref
     *
     * @return array
     */
    public function getBuyerFilterOrderRef()
    {
        return $this->session->getData("buyer_filter_order_ref");
    }

    /**
     * Using for buyer filter by From Date
     *
     * @return array
     */
    public function getBuyerFilterFromDate()
    {
        return $this->session->getData("buyer_filter_date_from");
    }

    /**
     * Using for buyer filter by To Date
     *
     * @return array
     */
    public function getBuyerFilterToDate()
    {
        return $this->session->getData("buyer_filter_date_to");
    }

    /**
     * Using for seller filter by RmaId
     *
     * @return array
     */
    public function getSellerFilterRmaId()
    {
        return $this->session->getData("seller_filter_rma_id");
    }

    /**
     * Using for seller filter by Status
     *
     * @return array
     */
    public function getSellerFilterStatus()
    {
        return (int) $this->session->getData("seller_filter_status");
    }

    /**
     * Using for seller filter by Order Ref
     *
     * @return array
     */
    public function getSellerFilterOrderRef()
    {
        return $this->session->getData("seller_filter_order_ref");
    }

    /**
     * Using for seller filter by From Date
     *
     * @return array
     */
    public function getSellerFilterFromDate()
    {
        return $this->session->getData("seller_filter_date_from");
    }

    /**
     * Using for seller filter by T Date
     *
     * @return array
     */
    public function getSellerFilterToDate()
    {
        return $this->session->getData("seller_filter_date_to");
    }

    /**
     * Get Seller Filter
     *
     * @return array
     */
    public function getSellerFilterCustomer()
    {
        return $this->session->getData("seller_filter_customer");
    }

     /**
      * Get All Status of RMA
      *
      * @return array
      */
    public function getAllRmaStatus()
    {
        $allStatus = [
                        self::FILTER_STATUS_ALL => "All Status",
                        self::FILTER_STATUS_PENDING => "Pending",
                        self::FILTER_STATUS_PROCESSING => "Processing",
                        self::FILTER_STATUS_SOLVED => "Solved",
                        self::FILTER_STATUS_DECLINED => "Declined",
                        self::FILTER_STATUS_CANCELED => "Canceled"
                    ];

        return $allStatus;
    }

     /**
      * Get RMA details According to customer
      *
      * @param string $type
      * @return string
      */
    public function getMessage($type = self::TYPE_BUYER)
    {
        if ($type == self::TYPE_SELLER) {
            $allKeys = [
                    "seller_filter_rma_id",
                    "seller_filter_order_ref",
                    "seller_filter_status",
                    "seller_filter_date_from",
                    "seller_filter_date_to",
                    "seller_filter_customer"
                ];
            $msg = __("You have no requested RMA.");
        } else {
            $allKeys = [
                    "buyer_filter_rma_id",
                    "buyer_filter_order_ref",
                    "buyer_filter_status",
                    "buyer_filter_date_from",
                    "buyer_filter_date_to"
                ];
            $msg = __("You didn't request any RMA.");
        }

        foreach ($allKeys as $key) {
            if ($this->session->getData($key)) {
                return __("No RMA found.");
            }
        }

        return __($msg);
    }

     /**
      * RMA Filters according to customers
      *
      * @param array $collection
      * @param string $type
      * @return arrayobject
      */
    public function applyFilter($collection, $type = self::TYPE_BUYER)
    {
        if ($type == self::TYPE_BUYER) {
            $rmaId = $this->getBuyerFilterRmaId();
            $status = $this->getBuyerFilterStatus();
            $orderRef = $this->getBuyerFilterOrderRef();
            $fromDate = $this->getBuyerFilterFromDate();
            $toDate = $this->getBuyerFilterToDate();
        } else {
            $rmaId = $this->getSellerFilterRmaId();
            $status = $this->getSellerFilterStatus();
            $orderRef = $this->getSellerFilterOrderRef();
            $fromDate = $this->getSellerFilterFromDate();
            $toDate = $this->getSellerFilterToDate();
        }

        if ($rmaId != "") {
            $collection->addFieldToFilter('id', $rmaId);
        }

        if ($orderRef != "") {
            $collection->addFieldToFilter('order_ref', ["like" => "%$orderRef%"]);
        }

        if ($fromDate != "" || $toDate != "") {
            if ($fromDate == "" && $toDate !== "") {
                $sql = "(date(created_date) <= '$toDate')";
                $collection->getSelect()->where($sql);
            } elseif ($fromDate != "" && $toDate == "") {
                $sql = "(date(created_date) >= '$fromDate')";
                $collection->getSelect()->where($sql);
            } else {
                $sql = "(date(created_date) >= '$fromDate' and date(created_date) <= '$toDate')";
                $collection->getSelect()->where($sql);
            }
        }

        $collection = $this->applyStatusFilter($collection, $status);

        if ($type == self::TYPE_SELLER) {
            $customer = $this->getSellerFilterCustomer();
            if ($customer != "") {
                $collection->addFieldToFilter('customer_name', ["like" => "%$customer%"]);
            }
        }

        return $collection;
    }

    /**
     * Apply Filter according to RMA Status
     *
     * @param array $collection
     * @param int $status
     * @return array
     */
    public function applyStatusFilter($collection, $status)
    {
        if ($status != self::FILTER_STATUS_ALL) {
            $sql = "final_status > 0";
            if ($status == self::FILTER_STATUS_PENDING) {
                $sql = "(final_status = 0 and seller_status = 0)";
            } elseif ($status == self::FILTER_STATUS_PROCESSING) {
                $sql = "(final_status = 0 and seller_status = 1)";
            } elseif ($status == self::FILTER_STATUS_SOLVED) {
                $sql = "(final_status = 3) or (final_status = 4) or ((final_status = 0 and seller_status = 2))";
            } elseif ($status == self::FILTER_STATUS_DECLINED) {
                $sql = "(final_status = 2) or (seller_status = 3 and final_status = 0)";
            } elseif ($status == self::FILTER_STATUS_CANCELED) {
                $sql = "(final_status = 1) or (seller_status = 4 and final_status = 0)";
            }

            $collection->getSelect()->where($sql);
        }

        return $collection;
    }

    /**
     * Get Sorting Type According To Customer
     *
     * @param string $type
     * @return string
     */
    public function getSortingOrder($type = self::TYPE_BUYER)
    {
        if ($type == self::TYPE_BUYER) {
            $sortingOrder = $this->session->getData("buyer_grid_sorting_order");
        } else {
            $sortingOrder = $this->session->getData("seller_grid_sorting_order");
        }

        if ($sortingOrder == "") {
            $sortingOrder = "DESC";
        }

        return $sortingOrder;
    }

    /**
     * Get Field According To Customer
     *
     * @param string $type
     * @return string
     */
    public function getSortingField($type = self::TYPE_BUYER)
    {
        if ($type == self::TYPE_BUYER) {
            $field = $this->session->getData("buyer_grid_sorting_field");
        } else {
            $field = $this->session->getData("seller_grid_sorting_field");
        }

        if ($field == "") {
            $field = "id";
        }

        return $field;
    }

     /**
      * Get Field Class According To Customer
      *
      * @param string $type
      * @return string
      */
    public function getSortingFieldClass($type = self::TYPE_BUYER)
    {
        $field = $this->getSortingField($type);
        if ($field == "order_ref") {
            $class = "wk-filtered-order-ref";
        } elseif ($field == "created_date") {
            $class = "wk-filtered-date";
        } elseif ($field == "customer_name") {
            $class = "wk-filtered-rma-customer";
        } else {
            $class = "wk-filtered-rma-id";
        }

        return $class;
    }

    /**
     * Get SortingOrder Class According To Customer
     *
     * @param string $type
     * @return string
     */
    public function getSortingOrderClass($type = self::TYPE_BUYER)
    {
        $sortingOrder = $this->getSortingOrder($type);
        if ($sortingOrder == "ASC") {
            $class = "wk-asc-order";
        } else {
            $class = "wk-desc-order";
        }

        return $class;
    }

    /**
     * Get Current Currency Code Which Is Set
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }

    /**
     * Convert Order Price according to Current currency
     *
     * @param array $order
     * @param int/float $price
     * @return int/float
     */
    public function getConvertedPrice($order, $price)
    {
        $currentCurrencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $baseCurrencyCode = $order->getBaseCurrencyCode();
        $orderCurrencyCode = $order->getOrderCurrencyCode();

        if ($currentCurrencyCode == $orderCurrencyCode) {
            return $price;
        }

        $price = $this->convertPriceToBase($price, $orderCurrencyCode);
        $price = $this->convertPriceFromBase($price, $currentCurrencyCode);
        return $price;
    }

    /**
     * Convert Price From Base function
     *
     * @param int $amount
     * @param string $currency
     * @return int|float
     */
    public function convertPriceFromBase($amount, $currency)
    {
        $store = $this->storeManager->getStore();
        return $store->getBaseCurrency()->convert($amount, $currency);
    }

    /**
     * Convert Price To Base function
     *
     * @param int $amount
     * @param string $currency
     * @return int|float
     */
    public function convertPriceToBase($amount, $currency)
    {
        $rate = $this->getCurrencyRateToBase($currency);
        $amount = $amount * $rate;
        return $amount;
    }

    /**
     * Get currency
     *
     * @param string $currency
     * @return int|float
     */
    public function getCurrencyRateToBase($currency)
    {
        $store = $this->storeManager->getStore();
        $baseCurrencyCode = $store->getBaseCurrency()->getCode();
        $rate = $this->currencyFactory->create()
                    ->load($currency)
                    ->getAnyRate($baseCurrencyCode);
        return $rate;
    }

    /**
     * Get Price With Currency
     *
     * @param float $price
     *
     * @return string
     */
    public function getPriceWithCurrency($price)
    {
        $price = $this->priceCurrency->convertAndFormat($price);
        return $price;
    }

    /**
     * Get User/return Qty in RMA table
     *
     * @param int $itemId
     * @param int $orderId
     * @param int $type
     * @return int
     */
    public function getUsedRmaQty($itemId, $orderId, $type)
    {
        $totalQty = 0;
        $collection = $this->detailsCollection->create();
        $tableName = $this->resource->getTableName('marketplace_rma_items');
        $sql = "main_table.id = rma_items.rma_id ";
        $collection->getSelect()->join(['rma_items' => $tableName], $sql, ['*']);
        $collection->addFilterToMap('item_id', 'rma_items.item_id');
        $condition = "((";
        $condition .= "(rma_items.item_id = $itemId)";
        $condition .= " AND (order_id = $orderId)";
        $condition .= " AND (final_status = 0)";
        $condition .= ")";
        $condition .= " OR ";
        $condition .= " (";
        $condition .= " (rma_items.is_qty_returned = 1)";
        $condition .= " AND (rma_items.item_id = $itemId)";
        $condition .= "))";
        if ($type == 3) {
            $condition .= " AND (resolution_type = 3)";
        } else {
            $condition .= " AND (resolution_type <= 2)";
        }

        $collection->getSelect()->where($condition);
        $collection->getSelect()->group("main_table.id");
        foreach ($collection as $item) {
            $totalQty += $item->getQty();
        }

        return $totalQty;
    }

    /**
     * Get Available Qty in RMA table
     *
     * @param int $itemId
     * @param int $orderId
     * @param int $totalQty
     * @param int $type
     * @return int
     */
    public function getAvailableRmaQty($itemId, $orderId, $totalQty, $type)
    {
        $qty = 0;
        $collection = $this->invoiceItemCollection
                            ->create()
                            ->addFieldToFilter('order_item_id', $itemId);
        foreach ($collection as $item) {
            $qty += $item->getQty();
        }

        $useInvoice = false;
        if ($type == 1) {
            $totalQty = $qty;
        } elseif ($type == 2) {
            $totalQty = $qty;
        } elseif ($type == 3) {
            $totalQty -= $qty;
        }

        $usedQty = $this->getUsedRmaQty($itemId, $orderId, $type);
        $availableQty = $totalQty - $usedQty;
        return $availableQty;
    }
    
     /**
      * Get Is Separate Seller Allow
      *
      * @return boolean
      */
    public function getIsSeparatePanel()
    {
        return $this->mpHelper->getIsSeparatePanel();
    }

    /**
     * Is Webkul_MpAssignProduct Active
     *
     * @return boolean
     */
    public function isMpAssign()
    {
        return $this->_moduleManager->isEnabled('Webkul_MpAssignProduct');
    }

    /**
     * If MpAssign Module Enable Then get sellerId of the product
     *
     * @param int $orderId
     * @return int
     */
    public function isMpAssignOrderSeller($orderId)
    {
        $sellerId = 0;
        $trackingsdata = $this->mpOrders->create()
            ->getCollection()
            ->addFieldToFilter(
                'order_id',
                $orderId
            );
        foreach ($trackingsdata as $tracking) {
            $sellerId = $tracking->getSellerId();
        }
        return $sellerId;
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->jsonHelper->jsonEncode($data, true);
    }

    /**
     * Get customer
     *
     * @param int $rmaId
     * @return void
     */
    public function getCustmerByRmaId($rmaId)
    {
        $rma = $this->getRmaDetails($rmaId);
        $customerId = $rma->getCustomerId();
        // Guest User check for admin
        if ($customerId == 0) {
            return true;
        }
        $customer = $this->getCustomer($customerId);
        if (empty($customer->getId())) {
            return false;
        }
        return true;
    }

    /**
     * Get marketplace orders
     *
     * @param $int $orderId
     * @param $int $sellerId
     *
     * @return Webkul\Marketplace\Model\Orders
     */
    public function getMpOrder($orderId, $sellerId)
    {
        $orderColl = $this->mpOrders->create()->getCollection()
                                            ->addFieldToFilter('order_id', $orderId)
                                            ->addFieldToFilter('seller_id', $sellerId);
        return $orderColl->getFirstItem();
    }

    /**
     * Get Invoice data
     *
     * @param $int $invoiceId
     *
     * @return Magento\Sales\Model\Order\Invoice
     */
    public function getInvoiceData($invoiceId)
    {
        $invoiceCollection = $this->invoice->create()->load($invoiceId);
        return $invoiceCollection;
    }

    /**
     * Get Rma Items
     *
     * @param int $rmaId
     * @return \Webkul\MpRmaSystem\Model\Items
     */
    public function getRmaItems($rmaId)
    {
        $rmaColl = $this->items->create()->load($rmaId);
        return $rmaColl;
    }

     /**
      * Get Creditmemo data by Order Id
      *
      * @param int $orderId
      * @return CreditmemoInterface[]|null
      */
    public function getCreditMemoByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
       
        $creditmemos = $this->creditmemoRepository->getList($searchCriteria);
            $creditmemoRecords = $creditmemos->getItems();
        return $creditmemoRecords;
    }
    
    /**
     * Get Credit memo Details by OrderID
     *
     * @param int $orderId
     * @return boolean
     */
    public function creditMemoByOrderId($orderId)
    {
        $record = false;
        $creditmemos = $this->getCreditMemoByOrderId($orderId);
        if (!empty($creditmemos)) {
            foreach ($creditmemos as $creditmemo) {
                $creditmemoRecords = $creditmemo;
                $record = true;
            }
        } else {
            $record = false;
        }
        return $record;
    }

    /**
     * Get Rma
     *
     * @param int $orderId
     * @return $collection
     */
    public function getRmaByOrderId($orderId)
    {
        return $collection = $this->detailsCollection->create()
        ->addFieldToFilter('order_id', $orderId);
    }

    /**
     * Get module status
     *
     * @return bool
     */
    public function getModuleStatus()
    {
        $path = "mprmasystem/settings/status";
        $scope = ScopeInterface::SCOPE_STORE;
        return $this->scopeConfig->getValue($path, $scope);
    }

    /**
     * Function for creating order
     *
     * @param array $rmaData
     * @param int $quoteId
     * @param object $order
     * @return int $orderId
     */
    public function createOrder($rmaData, $quoteId, $order)
    {
        $lastOrderId = $order->getId();
        $quote = $this->quoteFactory->create()->load($quoteId);
        $cartId = $this->cartManagementInterface->createEmptyCart();
        //Create empty cart
        $newQuote = $this->cartRepositoryInterface->get($cartId);
        $newQuote->setStore($quote->getStore());
        // if you have allready buyer id then you can load customer directly
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($customerId) {
            $customer = $this->customerRepository->getById($customerId);
            $newQuote->assignCustomer($customer); //Assign quote to customer
            $newQuote->setCustomer($customer);
        } else {
            $newQuote->setCustomerFirstname($quote->getCustomerFirstname());
            $newQuote->setCustomerLastname($quote->getCustomerLastname());
            $newQuote->setCustomerEmail($quote->getCustomerEmail());
            $newQuote->setCustomerIsGuest(true);
        }
        
        $orderItems = $order->getAllItems();
        $configProArr = [];
        foreach ($orderItems as $item) {
            $productId = $item->getProductId();
            $_product = $this->product->create()->load($productId);
            $buyRequest = $item->getProductOptions()["info_buyRequest"];
            $cancelQty = "";
            if (isset($rmaData["total_qty"][$item->getItemId()])) {
                $cancelQty = $rmaData["total_qty"][$item->getItemId()];
                $newQty = 0;
                if ($cancelQty!="") {
                    if ($cancelQty!=$item->getQtyOrdered()) {
                        $newQty = $item->getQtyOrdered()-$cancelQty;
                        $buyRequest["qty"] = $newQty;
                        $buyRequestData = new \Magento\Framework\DataObject($buyRequest);
                        $newQuote->addProduct($_product, $buyRequestData);
                    }
                } else {
                    $newQty = $item->getQtyOrdered();
                    $buyRequest["qty"] = $newQty;
                    $buyRequestData = new \Magento\Framework\DataObject($buyRequest);
                    $newQuote->addProduct($_product, $buyRequestData);
                }
                
            }
        }
        
        $quote->setIsActive(0);
        $quote->save();
        //set billing Address to quote
        $billingAddress = $quote->getBillingAddress()->getData();
        $newQuote->getBillingAddress()->setCustomerId($billingAddress['customer_id']);
        $newQuote->getBillingAddress()->setCustomerAddressId($billingAddress['customer_address_id']);
        $newQuote->getBillingAddress()->setAddressType($billingAddress['address_type']);
        $newQuote->getBillingAddress()->setFirstname($billingAddress['firstname']);
        $newQuote->getBillingAddress()->setLastname($billingAddress['lastname']);
        $newQuote->getBillingAddress()->setEmail($billingAddress['email']);
        $newQuote->getBillingAddress()->setStreet($billingAddress['street']);
        $newQuote->getBillingAddress()->setCity($billingAddress['city']);
        $newQuote->getBillingAddress()->setCountryId($billingAddress['country_id']);
        $newQuote->getBillingAddress()->setRegionId($billingAddress['region_id']);
        $newQuote->getBillingAddress()->setPostcode($billingAddress['postcode']);
        $newQuote->getBillingAddress()->setTelephone($billingAddress['telephone']);
        $newQuote->getBillingAddress()->setSameAsBilling($billingAddress['same_as_billing']);
        $newQuote->getBillingAddress()->setCompany($billingAddress['company']);
        
        //set shipping address to quote
        $shippingAddress = $quote->getShippingAddress()->getData();
        $newQuote->getShippingAddress()->setCustomerId($shippingAddress['customer_id']);
        $newQuote->getShippingAddress()->setCustomerAddressId($billingAddress['customer_address_id']);
        $newQuote->getShippingAddress()->setAddressType($shippingAddress['address_type']);
        $newQuote->getShippingAddress()->setFirstname($shippingAddress['firstname']);
        $newQuote->getShippingAddress()->setLastname($shippingAddress['lastname']);
        $newQuote->getShippingAddress()->setEmail($shippingAddress['email']);
        $newQuote->getShippingAddress()->setStreet($shippingAddress['street']);
        $newQuote->getShippingAddress()->setCity($shippingAddress['city']);
        $newQuote->getShippingAddress()->setCountryId($shippingAddress['country_id']);
        $newQuote->getShippingAddress()->setRegionId($shippingAddress['region_id']);
        $newQuote->getShippingAddress()->setPostcode($shippingAddress['postcode']);
        $newQuote->getShippingAddress()->setTelephone($shippingAddress['telephone']);
        $newQuote->getShippingAddress()->setSameAsBilling($shippingAddress['same_as_billing']);
        $newQuote->getShippingAddress()->setCompany($billingAddress['company']);
        
        // Collect Rates and Set Shipping & Payment Method
        $shippingMethod = $order->getShippingMethod();
        $newQuote->getShippingAddress()->setCollectShippingRates(true)
        ->collectShippingRates()
        ->setShippingMethod($shippingMethod);
        $couponCode = $order->getCouponCode();
        $newQuote->setCouponCode($couponCode);
        $this->cartRepositoryInterface->save($newQuote);
        $newQuote->setPaymentMethod($quote->getPayment()->getMethod());
        $newQuote->setInventoryProcessed(false);
        $newQuote->getPayment()->importData(['method' => $quote->getPayment()->getMethod()]);
        //apply discount to quote
        $newQuote->save();
        $newQuote->collectTotals();
        // Create Order From Quote
        $newQuote = $this->cartRepositoryInterface->get($newQuote->getId());
        $orderId = $this->cartManagementInterface->placeOrder($newQuote->getId());
        $order11 = $this->getOrder($orderId);
        $order11->setOrderApprovalStatus(1);
        $increment_id = $order11->getRealOrderId();
        $order11->save();
        if ($order11->getEntityId()) {
            $lastOrder = $this->getOrder($lastOrderId);
            //marketplace seller order cancel
            $sellerOrderCollection = $this->mpOrders->create()->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId);
            if ($sellerOrderCollection->getSize()) {
                foreach ($sellerOrderCollection->getData() as $sellerOrder) {
                    $sellerId = $sellerOrder["seller_id"];
                    $flag = $this->mpOrdersHelper->cancelorder($lastOrder, $sellerId);
                    if ($flag) {
                        $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
                        $paymentCode = '';
                        $paymentMethod = '';
                        if ($lastOrder->getPayment()) {
                            $paymentCode = $lastOrder->getPayment()->getMethod();
                        }
                        $orderId = $lastOrder->getEntityId();
                        $this->updateSellerOrderStatus($orderId, $sellerId, $paidCanceledStatus, $paymentCode);

                        $trackingcoll = $this->mpOrders->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingcoll as $tracking) {
                            $tracking->setTrackingNumber('canceled');
                            $tracking->setCarrierName('canceled');
                            $tracking->setIsCanceled(1);
                            $tracking->setOrderStatus('canceled');
                            $tracking->save();
                        }
                        $this->_eventManager->dispatch(
                            'mp_order_cancel_after',
                            ['seller_id' => $sellerId, 'order' => $lastOrder]
                        );
                    }
                }
            }
            $lastOrder = $this->getOrder($lastOrderId);
            $lastOrder->setStatus('canceled');
            $lastOrder->save();
            return $order11->getEntityId();
        }
        return false;
    }

    /**
     * Update Seller Order Status.
     *
     * @param int $orderId
     * @param int $sellerId
     * @param float $paidCanceledStatus
     * @param string $paymentCode
     * @return void
     */
    public function updateSellerOrderStatus($orderId, $sellerId, $paidCanceledStatus, $paymentCode)
    {
        $collection = $this->saleslistFactory->create()
        ->getCollection()
        ->addFieldToFilter(
            'order_id',
            ['eq' => $orderId]
        )
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $sellerId]
        );
        foreach ($collection as $saleproduct) {
            $saleproduct->setCpprostatus(
                $paidCanceledStatus
            );
            $saleproduct->setPaidStatus(
                $paidCanceledStatus
            );
            if ($paymentCode == 'mpcashondelivery') {
                $saleproduct->setCollectCodStatus(
                    $paidCanceledStatus
                );
                $saleproduct->setAdminPayStatus(
                    $paidCanceledStatus
                );
            }
            $saleproduct->save();
        }
    }
}
