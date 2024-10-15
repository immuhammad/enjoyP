<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Helper;

use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\UrlInterface;
use Webkul\Marketplace\Controller\Product\SaveProduct;
use Magento\Framework\App\Filesystem\DirectoryList;

class Quote extends \Magento\Framework\App\Helper\AbstractHelper
{
    const QUOTE_STATUS_WAITING = 0;
    const QUOTE_STATUS_PENDING = 1;
    const QUOTE_STATUS_PROCESSING = 2;
    const QUOTE_STATUS_APPROVED = 3;
    const QUOTE_STATUS_COMPLETE = 4;
    const QUOTE_STATUS_REJECTED = 5;
    const QUOTE_STATUS_CANCELED = 6;
    const QUOTE_STATUS_ANSWERED = 7;
    const QUOTE_STATUS_SENT_TO_SUPPLIERS = 8;

    const TYPE_BUYER = 0;
    const TYPE_SUPPLIER = 1;
    const TYPE_ADMIN = 2;
    const TYPE_ALL = 3;

    /**
     * @var \Magento\Framework\Data\Form\FormKey
     */
    protected $formKey;

    /**
     * Undocumented function
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Session\SessionManager $session
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Webkul\Marketplace\Model\SellerFactory $sellerFactory
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory
     * @param \Webkul\B2BMarketplace\Model\QuotationFactory $quotationFactory
     * @param \Webkul\B2BMarketplace\Model\LeadsFactory $leadsFactory
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewrite
     * @param SaveProduct $saveProduct
     * @param \Magento\Framework\Filesystem\Driver\File $fileDriver
     * @param \Magento\Framework\Image\AdapterFactory $imageFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Session\SessionManager $session,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Marketplace\Model\SellerFactory $sellerFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory,
        \Webkul\B2BMarketplace\Model\QuotationFactory $quotationFactory,
        \Webkul\B2BMarketplace\Model\LeadsFactory $leadsFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollectionFactory $urlRewrite,
        SaveProduct $saveProduct,
        \Magento\Framework\Filesystem\Driver\File $fileDriver,
        \Magento\Framework\Image\AdapterFactory $imageFactory,
        \Magento\Framework\Data\Form\FormKey $formKey,
        \Webkul\B2BMarketplace\Model\ProductFactory $b2bProductFactory
    ) {
        $this->_request = $context->getRequest();
        $this->_session = $session;
        $this->_mpHelper = $mpHelper;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_customerSession = $customerSession;
        $this->_messageManager = $messageManager;
        $this->_sellerFactory = $sellerFactory;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_resource = $resource;
        $this->_itemFactory = $itemFactory;
        $this->_quotationFactory = $quotationFactory;
        $this->_leadsFactory = $leadsFactory;
        $this->_urlInterface = $urlInterface;
        $this->_productFactory = $productFactory;
        $this->_filesystem = $filesystem;
        $this->_urlRewrite = $urlRewrite;
        $this->_saveProduct = $saveProduct;
        $this->_fileDriver = $fileDriver;
        $this->_imageFactory = $imageFactory;
        $this->_b2bProductFactory = $b2bProductFactory;
        $this->formKey = $formKey;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct($context);
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    public function getSupplierId()
    {
        return $this->_mpHelper->getCustomerId();
    }

    public function isLoggedIn()
    {
        return $this->_mpHelper->isCustomerLoggedIn();
    }

    public function getSellerQuoteSummary($paramsData)
    {
        $result = [];
        $supplierId = $this->getSupplierId();

        $collection = $this->getAllSellerQuoteItems($supplierId, self::QUOTE_STATUS_WAITING);
        $collection->addFieldToFilter("quote_item.status", self::QUOTE_STATUS_WAITING);
        $collection->addFieldToFilter('main_table.is_buying_lead', 0);
        if (isset($paramsData['product_name']) && $paramsData['product_name']) {
            $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$paramsData['product_name'].'%']);
        }
        if (isset($paramsData['customer_name']) && $paramsData['customer_name']) {
            $collection->addFieldToFilter('customer_name', $paramsData['customer_name']);
        }
        if (isset($paramsData['created_after']) && $paramsData['created_after']) {
            $date = date_create($paramsData['created_after']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
        }
        if (isset($paramsData['created_before']) && $paramsData['created_before']) {
            $date = date_create($paramsData['created_before']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
        }
        $count = $collection->getSize();
        $result[] = ["key" => "new", "label" => "New", "class" => "wk-summary-new-quote", "count" => $count];

        $collection = $this->getAllSellerQuoteItems($supplierId, self::QUOTE_STATUS_PENDING);
        $collection->addFieldToFilter("sent_quotes.status", self::QUOTE_STATUS_PENDING);
        if (isset($paramsData['product_name']) && $paramsData['product_name']) {
            $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$paramsData['product_name'].'%']);
        }
        if (isset($paramsData['customer_name']) && $paramsData['customer_name']) {
            $collection->addFieldToFilter('customer_name', $paramsData['customer_name']);
        }
        if (isset($paramsData['created_after']) && $paramsData['created_after']) {
            $date = date_create($paramsData['created_after']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
        }
        if (isset($paramsData['created_before']) && $paramsData['created_before']) {
            $date = date_create($paramsData['created_before']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
        }
        $count = $collection->getSize();
        $result[] = ["key" => "pending", "label" => "Pending",
        "class" => "wk-summary-pending-quote", "count" => $count];

        $collection = $this->getAllSellerQuoteItems($supplierId, self::QUOTE_STATUS_ANSWERED);
        $collection->addFieldToFilter("sent_quotes.status", self::QUOTE_STATUS_ANSWERED);
        if (isset($paramsData['product_name']) && $paramsData['product_name']) {
            $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$paramsData['product_name'].'%']);
        }
        if (isset($paramsData['customer_name']) && $paramsData['customer_name']) {
            $collection->addFieldToFilter('customer_name', $paramsData['customer_name']);
        }
        if (isset($paramsData['created_after']) && $paramsData['created_after']) {
            $date = date_create($paramsData['created_after']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
        }
        if (isset($paramsData['created_before']) && $paramsData['created_before']) {
            $date = date_create($paramsData['created_before']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
        }
        $count = $collection->getSize();
        $result[] = ["key" => "answered", "label" => "Answered",
        "class" => "wk-summary-answered-quote", "count" => $count];

        $collection = $this->getAllSellerQuoteItems($supplierId, self::QUOTE_STATUS_APPROVED);
        $collection->addFieldToFilter("sent_quotes.status", self::QUOTE_STATUS_APPROVED);
        if (isset($paramsData['product_name']) && $paramsData['product_name']) {
            $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$paramsData['product_name'].'%']);
        }
        if (isset($paramsData['customer_name']) && $paramsData['customer_name']) {
            $collection->addFieldToFilter('customer_name', $paramsData['customer_name']);
        }
        if (isset($paramsData['created_after']) && $paramsData['created_after']) {
            $date = date_create($paramsData['created_after']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
        }
        if (isset($paramsData['created_before']) && $paramsData['created_before']) {
            $date = date_create($paramsData['created_before']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
        }
        $count = $collection->getSize();
        $result[] = ["key" => "approved", "label" => "Confirmed",
        "class" => "wk-summary-confirmed-quote", "count" => $count];

        $collection = $this->getAllSellerQuoteItems($supplierId, self::QUOTE_STATUS_REJECTED);
        $collection->addFieldToFilter("sent_quotes.status", self::QUOTE_STATUS_REJECTED);
        if (isset($paramsData['product_name']) && $paramsData['product_name']) {
            $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$paramsData['product_name'].'%']);
        }
        if (isset($paramsData['customer_name']) && $paramsData['customer_name']) {
            $collection->addFieldToFilter('customer_name', $paramsData['customer_name']);
        }
        if (isset($paramsData['created_after']) && $paramsData['created_after']) {
            $date = date_create($paramsData['created_after']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
        }
        if (isset($paramsData['created_before']) && $paramsData['created_before']) {
            $date = date_create($paramsData['created_before']);
            $dateFormated = date_format($date, 'Y-m-d H:i:s');
            $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
        }
        $count = $collection->getSize();
        $result[] = ["key" => "rejected", "label" => "Rejected",
        "class" => "wk-summary-rejected-quote", "count" => $count];

        return $result;
    }

    public function getAllSellerQuoteItems($supplierId = 0, $status = 0)
    {
        $collection = $this->getQuoteModel()
            ->getCollection()
            ->getAllSellerQuoteBySellerId($supplierId, $status);

        return $collection;
    }

    public function getAllQuoteItems()
    {
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        return $collection;
    }

    public function getLabelClass($status)
    {
        if ($status == self::QUOTE_STATUS_PENDING) {
            $class = "wk-pending";
        } elseif ($status == self::QUOTE_STATUS_PROCESSING) {
            $class = "wk-processing";
        } elseif ($status == self::QUOTE_STATUS_APPROVED) {
            $class = "wk-approved";
        } elseif ($status == self::QUOTE_STATUS_COMPLETE) {
            $class = "wk-completed";
        } elseif ($status == self::QUOTE_STATUS_REJECTED) {
            $class = "wk-rejected";
        } elseif ($status == self::QUOTE_STATUS_CANCELED) {
            $class = "wk-cancelled";
        } elseif ($status == self::QUOTE_STATUS_ANSWERED) {
            $class = "wk-answered";
        } elseif ($status == self::QUOTE_STATUS_SENT_TO_SUPPLIERS) {
            $class = "wk-sent-to-suppliers";
        } else {
            $class = "wk-new";
        }

        return $class;
    }

    public function getLabel($status)
    {
        if ($status == self::QUOTE_STATUS_PENDING) {
            $label = "Pending";
        } elseif ($status == self::QUOTE_STATUS_PROCESSING) {
            $label = "Processing";
        } elseif ($status == self::QUOTE_STATUS_APPROVED) {
            $label = "Approved";
        } elseif ($status == self::QUOTE_STATUS_COMPLETE) {
            $label = "Completed";
        } elseif ($status == self::QUOTE_STATUS_REJECTED) {
            $label = "Rejected";
        } elseif ($status == self::QUOTE_STATUS_CANCELED) {
            $label = "Cancelled";
        } elseif ($status == self::QUOTE_STATUS_ANSWERED) {
            $label = "Answered";
        } elseif ($status == self::QUOTE_STATUS_SENT_TO_SUPPLIERS) {
            $label = "Sent To Suppliers";
        } else {
            $label = "New";
        }

        return __($label);
    }

    public function getStatusLabelClass($status)
    {
        return $this->getLabelClass($status);
    }

    public function getStatusLabel($status)
    {
        return __($this->getLabel($status));
    }

    public function getFormattedPrice($price, $flag = true)
    {
        return $this->_objectManager
            ->create(\Magento\Framework\Pricing\Helper\Data::class)
            ->currency($price, $flag, false);
    }

    public function isValidQuote($quoteId, $type = self::TYPE_BUYER)
    {
        $sellerId = $this->getSupplierId();
        $customerId = $this->getCustomerId();
        if ($type == self::TYPE_SUPPLIER) {
            $collection = $this->getQuoteModel()
                ->getCollection()
                ->getAllSellerQuoteBySellerId($sellerId);
            $collection->addFieldToFilter("quote_item.quote_id", $quoteId);

            if ($collection->getSize()) {
                return true;
            } else {
                // If RFQ is a buying lead.
                $collection = $this->getAllSentQuotes();
                $collection->addFieldToFilter("quote_id", $quoteId);
                $collection->addFieldToFilter("seller_id", $sellerId);
                if ($collection->getSize()) {
                    return true;
                }
            }
        } else {
            $collection = $this->getQuoteModel()
                ->getCollection()
                ->getAllCustomerQuote();
            $collection->addFieldToFilter("quote_item.quote_id", $quoteId);
            $collection->addFieldToFilter("main_table.customer_id", $customerId);
        }

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getQuoteModel()
    {
        return $this->_objectManager->create(\Webkul\B2BMarketplace\Model\Quote::class);
    }

    public function getQuote($quoteId = 0)
    {
        if ($quoteId == 0) {
            $quoteId = $this->getCurrentQuoteId();
        }

        $quote = $this->getQuoteModel()->load($quoteId);
        return $quote;
    }

    public function getSellerLabel()
    {
        return "seller";
    }

    public function getAdminLabel()
    {
        return "admin";
    }

    public function getBuyerLabel()
    {
        return "buyer";
    }

    public function getDefaultLabel()
    {
        return "you";
    }

    public function getCurrentQuoteId()
    {
        return $this->_request->getParam("id");
    }

    public function getCurrentQuoteItems()
    {
        $quoteId = (int) $this->_request->getParam("id");
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        $sql = "(quote.entity_id = $quoteId)";
   
        $collection->getSelect()->where($sql);
        $collection->addFieldToSelect("name");
        $collection->addFieldToSelect("entity_id");
        $collection->addFieldToSelect("quote_id");
        return $collection;
    }

    public function getCurrentQuoteItem()
    {
        $quoteId = (int) $this->_request->getParam("id");
        $itemId = (int) $this->_request->getParam("item_id");
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        $sql = "(quote.entity_id = $quoteId)";
        if ($itemId > 0) {
            $sql .= " and (main_table.entity_id = $itemId)";
        }
   
        $collection->getSelect()->where($sql);
        $collection->getSelect()->limit(1);

        foreach ($collection as $item) {
            return $item;
        }

        return $this->_itemFactory->create();
    }

    public function getCurrentQuoteItemId()
    {
        $itemId = (int) $this->_request->getParam("item_id");
        if ($itemId == 0) {
            $itemId = $this->getCurrentQuoteItem()->getEntityId();
        }

        return $itemId;
    }
    
    public function getQuoteItems($quoteId, $type = self::TYPE_ALL)
    {
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);
        return $collection;
    }

    public function getQuoteWithItems($type = self::TYPE_BUYER)
    {
        $quoteId = $this->getCurrentQuoteId();
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        $sql = "(quote.entity_id = $quoteId)";

        $itemId = $this->_request->getParam("item_id");
        if ($itemId > 0) {
            $sql = "(quote.entity_id = $quoteId) and (main_table.entity_id = $itemId)";
        }
        $collection->getSelect()->where($sql);
        return $collection;
    }

    public function getQuoteRequestDataOLd()
    {
        $itemId = $this->_request->getParam("id");
        $supplierId = $this->_request->getParam("supplier_id");

        $quoteId = $this->getCurrentQuoteId();
        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "entity_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        if ($type == self::TYPE_ADMIN) {
            $sql = "((quote.entity_id = $quoteId) and (main_table.seller_id = 0))";
        }

        if ($type == self::TYPE_SUPPLIER) {
            $sql = "((quote.entity_id = $quoteId) and (main_table.seller_id = ".$this->getSupplierId()."))";
        }

        if ($type == self::TYPE_BUYER) {
            $sql = "(quote.entity_id = $quoteId)";
        }

        $collection->getSelect()->where($sql);

        return $collection;
    }

    public function getQuoteButtonHtml($quote, $type = 0)
    {
        $html = "";
        $quoteId = $quote->getId();
        
        if ($type != 1) {
            $url = $this->_storeManager->getStore()->getBaseUrl();
            $url .= "mpquotesystem/buyerquote/edit/id/$quoteId/";
            $html .= '<a href="'.$url.'" class="wk-edit-quote-link">Edit Quote</a>';
        }

        return $html;
    }

    public function getItemStatusLabelHtml($mpQuoteItem)
    {
        $quoteItem = $this->_itemFactory->create()->load($mpQuoteItem->getId());
        $label = $this->getLabel($quoteItem->getStatus());
        $html = "";
        $html .= "<div class='wk-qs-item-status-label wk-qs-item-status-label-".strtolower($label)."'>";
        $html .= $label;
        $html .= "</div>";
        return $html;
    }

    public function getBuyerQuoteItemButtonHtml($quoteItem)
    {
        try {
            $status = $quoteItem->getStatus();
            $quoteItemId = $quoteItem->getEntityId();
            $quoteId = $quoteItem->getQuoteId();
            $requestId = $quoteItem->getRequestId();
            
            $collection = $this->_quotationFactory->create()->getCollection();
            $collection->addFieldToFilter("quote_id", $quoteId);
            $collection->addFieldToFilter("quote_item_id", $quoteItemId);
            $collection->addFieldToFilter("request_id", $requestId);
            $collection->setOrder("entity_id", "DESC");
            $collection->getSelect()->limit(1);
            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $status = $item->getStatus();
                }
            } else {
                return "<div class='wk-b2b-purchase-quote-block'></div>";
            }

            $html = "<div class='wk-b2b-purchase-quote-block'>";
            if ($status == self::QUOTE_STATUS_APPROVED) {
                if ($this->isItemInCart($quoteItem)) {
                    $html .= "<div class='wk-qs-item-action-status-label'>Added In Cart</div>";
                } else {
                    $html .= "<form method='post' action='".
                    $this->_urlInterface->getUrl("b2bmarketplace/quote/purchase")."'>";
                    $html .= "<input type='hidden' name='quote_id' value='".$quoteId."'>";
                    $html .= "<input type='hidden' name='item_id' value='".$quoteItemId."'>";
                    $html .= "<input type='hidden' name='request_id' value='".$requestId."'>";
                    $html .= "<input type='hidden' name='form_key' value='".$this->getFormKey()."'>";
                    $html .= "<button class='action tocart primary'><span>".
                    __('Add To Cart')."</span></button>";
                    $html .= "</form>";
                }
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_REJECTED) {
                $html .= "";
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_CANCELED) {
                $html .= "";
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_COMPLETE) {
                $html .= "";
            } else {
                $html .= "";
            }

            if ($this->hasQuotations($quoteItem->getEntityId())) {
                $html .= "<button class='wk-view-quote-btn action tocart primary' data-id='".
                $quoteItem->getEntityId()."'><span>View Quotes</span></button>";
            }
            $html .= "</div>";

        } catch (\Exception $e) {
            $html = "<div class='wk-b2b-purchase-quote-block'></div>";
            ;
        }

        return $html;
    }

    public function getQuoteItemButtonHtml($quoteItem, $type = 0)
    {
        $status = $quoteItem->getStatus();
        $requestStatus = $quoteItem->getRequestStatus();
        $quoteItem = $this->_itemFactory->create()->load($quoteItem->getEntityId());
        $html = "";

        if ($type == 2) {
            if ($quoteItem->getSellerId() == 0) {
                if ($quoteItem->getStatus() == self::QUOTE_STATUS_APPROVED) {
                    $html .= "";
                } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_CANCELED) {
                    $html .= "";
                } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_COMPLETE) {
                    $html .= "";
                } else {
                    $html .= "<button name='approve_item' value='".$quoteItem->getId().
                    "' class='wk-approve-quote-btn wk-approve-quote-item-btn'><span>View Quote</span></button>";
                }
            }
        } elseif ($type == 1) { // Seller view
            if ($this->hasQuotations($quoteItem->getEntityId())) {
                $html .= "<button class='wk-view-quote-btn action tocart primary' data-id='".
                $quoteItem->getEntityId()."'><span>View Quotes</span></button>";
            } else {
                $html .= "<a href='".$this->_urlInterface->getUrl(
                    "b2bmarketplace/supplier/quote_request",
                    ['id' => $quoteItem->getQuoteId(), 'item_id' =>$quoteItem->getEntityId()]
                )
                ."' class='wk-quote-product-btn'>Quote Product</a>";
            }
        } else {
            if ($quoteItem->getStatus() == self::QUOTE_STATUS_APPROVED) {
                if ($this->isItemInCart($quoteItem)) {
                    $html .= "<div class='wk-qs-item-action-status-label'>Added In Cart</div>";
                } else {
                    $html .= "<div class='wk-b2b-purchase-quote-block'>";
                    $html .= "<form method='post' action='".
                    $this->_urlInterface->getUrl("b2bmarketplace/quote/purchase")."'>";
                    $html .= "<input type='hidden' name='quote_id' value='".$quoteItem->getQuoteId()."'>";
                    $html .= "<input type='hidden' name='item_id' value='".$quoteItem->getEntityId()."'>";
                    $html .= "<button class='action tocart primary'><span>Add To Cart</span></button>";
                    $html .= "</form>";
                    $html .= "</div>";

                }
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_REJECTED) {
                $html .= "";
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_CANCELED) {
                $html .= "";
            } elseif ($quoteItem->getStatus() == self::QUOTE_STATUS_COMPLETE) {
                $html .= "";
            } else {
                $html .= "";
            }
        }

        return $html;
    }

    public function isItemInCart()
    {
        return false;
    }

    public function getNewDirectoryImage($src)
    {
        $segments = array_reverse(explode('/', $src));
        $first_dir = substr($segments[0], 0, 1);
        $second_dir = substr($segments[0], 1, 1);
        return 'cache/'.$first_dir.'/'.$second_dir.'/'.$segments[0];
    }

    public function isValidQuoteItem($itemId, $type = self::TYPE_BUYER)
    {
        $collection = $this->getAllQuoteItems();
        $quoteId = $this->getCurrentQuoteId();
        $collection->addFieldToFilter("main_table.entity_id", $itemId);
        $collection->addFieldToFilter("quote_id", $quoteId);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getAllSentQuotes($itemId = 0)
    {
        if ($itemId == 0) {
            $itemId = $this->_request->getParam("id");
        }

        $collection = $this->_quotationFactory->create()->getCollection();
        $collection->addFieldToFilter("quote_item_id", $itemId);
        return $collection;
    }

    public function getCount($status)
    {
        $customerId = $this->getCustomerId();
        $quoteItem = $this->getCurrentQuoteItem();
        $itemId = $quoteItem->getEntityId();
        $collection = $this->_quotationFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);
        $collection->addFieldToFilter("quote.customer_id", $customerId);
        $collection->addFieldToFilter("main_table.quote_item_id", $itemId);
        $collection->addFieldToFilter("main_table.status", $status);

        return $collection->getSize();
    }

    public function getTabsContent()
    {
        $content = [];
        $content["new"] = $this->getQuotation(self::QUOTE_STATUS_WAITING);
        $content["pending"] = $this->getQuotation(self::QUOTE_STATUS_PENDING);
        $content["answered"] = $this->getQuotation(self::QUOTE_STATUS_ANSWERED);
        $content["approved"] = $this->getQuotation(self::QUOTE_STATUS_APPROVED);
        $content["rejected"] = $this->getQuotation(self::QUOTE_STATUS_REJECTED);

        return $content;
    }

    public function getQuotation($status)
    {
        $customerId = $this->getCustomerId();
        $itemId = $this->getCurrentQuoteItemId();
        $collection = $this->_quotationFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        $tableName = $this->_resource->getTableName('customer_entity');
        $sql = "main_table.seller_id = supplier.entity_id ";
        $columns = [];
        $columns["supplier_firstname"] = "firstname";
        $columns["supplier_lastname"] = "lastname";
        $field = 'CONCAT(supplier.firstname, " ", supplier.lastname)';
        $columns['supplier_name'] = new \Zend_Db_Expr($field);

        $field = 'count(main_table.seller_id)';
        $columns['thread'] = new \Zend_Db_Expr($field);

        $field = 'max(main_table.created_at)';
        $columns['created_at'] = new \Zend_Db_Expr($field);

        $collection->getSelect()->joinLeft(['supplier' => $tableName], $sql, $columns);

        $collection->addFieldToFilter("quote.customer_id", $customerId);
        $collection->addFieldToFilter("main_table.quote_item_id", $itemId);
        $collection->addFieldToFilter("main_table.status", $status);
        $collection->getSelect()->group("main_table.seller_id");
        return $collection;
    }

    public function getQuoteItem()
    {
        $itemId = $this->_request->getParam("id");
        return $this->_itemFactory->create()->load($itemId);
    }

    public function getQuoteRequestData($type = self::TYPE_BUYER)
    {
        $itemId = $this->_request->getParam("id");
        $supplierId = $this->_request->getParam("supplier_id");

        $collection = $this->_quotationFactory->create()->getCollection();
        $collection->addFieldToFilter("seller_id", $supplierId);

        $collection->addFieldToSelect("quote_id");
        $collection->addFieldToSelect("quote_item_id");

        $collection->getSelect()->group("quote_item_id");

        $quoteId = 0;
        $itemsIds = [];
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $quoteId = $item->getQuoteId();
                $itemsIds[] = $item->getQuoteItemId();
            }
        }

        $collection = $this->_itemFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote');
        $sql = "quote.entity_id = main_table.quote_id ";
        $columns = [];
        $columns[] = "customer_id";
        $collection->getSelect()->join(['quote' => $tableName], $sql, $columns);

        if ($quoteId > 0) {
            $collection->addFieldToFilter("main_table.quote_id", $quoteId);
            $collection->addFieldToFilter("main_table.entity_id", ["in" => $itemsIds]);
        }

        return $collection;
    }

    public function getTableName($tableName)
    {
        return $this->_resource->getTableName($tableName);
    }

    public function forwardQuoteToSuppliers($quoteId, $forceForward = false)
    {
        $quoteItems = $this->getQuoteItems($quoteId);
        if (!$forceForward) {
            $quoteItems->addFieldToFilter("buying_lead_status", ["eq" => 1]);
        }

        $quoteItems->addFieldToSelect("entity_id");
        $quoteItems->addFieldToSelect("quote_id");

        $suppliers = $this->getAllSuppliers();
        $suppliers->addFieldToSelect("seller_id");
        $itemDetails = [];
        
        $supplierIds = [];
        foreach ($suppliers as $supplier) {
            $supplierIds[] = $supplier->getSellerId();
        }

        $data = [];
        foreach ($quoteItems as $quoteItem) {
            $quoteId = $quoteItem->getQuoteId();
            $quoteItemId = $quoteItem->getEntityId();
            foreach ($supplierIds as $supplierId) {
                $info = [];
                $info[] = ["quote_id" => $quoteId, "quote_item_id" => $quoteItemId, "seller_id" => $supplierId];
                $data[] = $info;
            }
        }

        try {
            $connection = $this->_objectManager
            ->create(\Magento\Framework\Module\ModuleResource::class)->getConnection();
            $connection->beginTransaction();
            $connection->insertMultiple($this->_resource->getTableName('wk_b2b_requestforquote_buying_leads'), $data);
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    public function getAllSuppliers()
    {
        $collection = $this->_sellerFactory
                            ->create()
                            ->getCollection();

        $tableName = $this->_resource->getTableName('customer_entity');
        $sql = "customer.entity_id = main_table.seller_id";
        $collection->getSelect()->join(['customer' => $tableName], $sql, ['email']);

        $collection->addFieldToFilter("main_table.is_seller", 1);
        $collection->getSelect()->group("main_table.seller_id");
        return $collection;
    }

    public function hasQuotations($itemId)
    {
        $sellerId = $this->getSupplierId();
        $collection = $this->_quotationFactory->create()->getCollection();
        $collection->addFieldToFilter("quote_item_id", $itemId);
        $collection->addFieldToFilter("seller_id", $sellerId);

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getCustomer($customerId = 0)
    {
        if ($customerId == 0) {
            return $this->_customerSession->getCustomer();
        }

        return $this->_customerFactory->create()->load($customerId);
    }

    /**
     * Get Mediad Path.
     *
     * @return string
     */
    public function getMediaPath()
    {
        return $this->_filesystem
                    ->getDirectoryRead(DirectoryList::MEDIA)
                    ->getAbsolutePath();
    }

    /**
     * Add Image to Preorder Complete Product
     *
     * @param object $product
     */
    public function addImage($product, $quoteItem)
    {
        $path = $this->getMediaPath().'tmp/catalog/product';
        $samples = trim($quoteItem->getSamples());
        if ($samples != "") {
            $images = [];
            if (strpos($samples, ",") !== false) {
                $samples = explode(",", $samples);
            } else {
                $samples = [$samples];
            }

            foreach ($samples as $k => $sample) {
                $image = $path.$sample;
                if ($this->_fileDriver->isExists($image)) {
                    try {
                        $types = ['image', 'small_image', 'thumbnail'];
                        $product->addImageToMediaGallery($image, $types, false, false);
                    } catch (\Exception $e) {
                        $e->getMessage();
                    }
                }
            }
            $product->save();
        }
    }

    public function createQuoteProduct($quoteItem)
    {
        try {
            $quoteItemId = $quoteItem["quote_item_id"];
            $productId = $this->_itemFactory
                                ->create()
                                ->load($quoteItemId)
                                ->getProductId();

            $taxClassId = $this->_productFactory
                                ->create()
                                ->load($productId)
                                ->getTaxClassId();
            $result = [];
            $attributeSetId = $this->_productFactory->create()->getDefaultAttributeSetId();
            $urlKey = preg_replace('/\s+/', '-', $quoteItem->getName());
            $urlKey = $urlKey."-".$quoteItem->getEntityId();
            $createdUrl = $this->createUrl($urlKey, $urlKey, 0);
            $product = [];
            $product['name'] = $quoteItem->getName();
            $product['sku'] = $quoteItem->getName()."-".$quoteItem->getEntityId();
            $product['price'] = $quoteItem->getPrice();
            $product['url_key'] = $createdUrl;
            $product['mp_product_cart_limit'] = $quoteItem->getQty();
            $product['stock_data'] = [
                'manage_stock' => 1,
                'use_config_manage_stock' => 1,
                'min_sale_qty' => $quoteItem->getQty(),
                'max_sale_qty' => $quoteItem->getQty()
            ];
            $product['quantity_and_stock_status'] = [
                'qty' => $quoteItem->getQty(),
                'is_in_stock'=>1
            ];
            $product['weight'] = 1;
            $product['product_has_weight'] = 1;
            $product['tax_class_id'] = $taxClassId;
            $product['visibility'] = 1;

            $samples = trim($quoteItem->getSamples());
            if ($samples != "") {
                $images = [];
                if (strpos($samples, ",") !== false) {
                    $samples = explode(",", $samples);
                    foreach ($samples as $k => $sample) {
                        $path = $this->getMediaPath().'tmp/catalog/product';
                        $destinationPath = $path.$sample;
                        if ($this->_fileDriver->isExists($destinationPath)) {
                            $image = [];
                            $image['position'] = $k + 1;
                            $image['file'] = $sample;
                            $image['disabled'] = 0;
                            $image['label'] = "";
                            $images[] = $image;
                            $product['image'] = $sample;
                            $product['small_image'] = $sample;
                            $product['thumbnail'] = $sample;
                        }
                    }
                }
                $product['media_gallery']['images'] = $images;
            }
            $wholeData = [];
            $wholeData['type'] = "simple";
            $wholeData['set'] = $attributeSetId;
            $wholeData['status'] = 1;
            $wholeData['product'] = $product;
            $result = $this->_saveProduct->saveProductData($quoteItem->getSellerId(), $wholeData);
            $result['error'] = false;
        } catch (\Exception $e) {
            $result = ["error" => true, "error" => $e->getMessage()];
        }
        return $result;
    }

    /**
     * Get All Website Ids.
     *
     * @return array
     */
    public function getWebsiteIds()
    {
        $websiteIds = [];
        $websites = $this->_storeManager->getWebsites();
        foreach ($websites as $website) {
            $websiteIds[] = $website->getId();
        }

        return $websiteIds;
    }

    public function generateQuoteRequestId($quoteId, $supplierId)
    {
        $requestId = $this->getQuoteRequestId($quoteId, $supplierId);

        if ($requestId == 0) {
            $collection = $this->_quotationFactory->create()->getCollection();
            $collection->addFieldToSelect("request_id");
            $collection->setOrder("request_id", "DESC");
            $collection->getSelect()->limit(1);

            if ($collection->getSize()) {
                foreach ($collection as $item) {
                    $requestId = $item->getRequestId() + 1;
                }
                return $requestId;
            }

            $requestId = 1;
        }

        return $requestId;
    }

    public function getQuoteRequestId($quoteId, $supplierId)
    {
        $requestId = 0;
        $collection = $this->_quotationFactory->create()->getCollection();
        $collection->addFieldToFilter("quote_id", $quoteId);
        $collection->addFieldToFilter("seller_id", $supplierId);
        $collection->getSelect()->limit(1);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                return $item->getRequestId();
            }
        }

        return $requestId;
    }

    public function isValidSupplierQuote($itemId, $supplierId)
    {
        $collection = $this->_quotationFactory->create()->getCollection();
        $collection->addFieldToFilter("quote_item_id", $itemId);
        $collection->addFieldToFilter("seller_id", $supplierId);
        $collection->getSelect()->limit(1);
        if ($collection->getSize()) {
            foreach ($collection as $item) {
                return true;
            }
        }

        return false;
    }

    public function isValidRequest($requestId)
    {
        $requestId = (int) $this->_request->getParam("id");
        $collection = $this->_itemFactory->create()
            ->getCollection()
            ->addFieldToFilter("quote.customer_id", $this->getCustomerId())
            ->getBuyerQuoteWithItems($requestId);
        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function getRequestQuoteId()
    {
        return $this->_request->getParam("id");
    }

    public function getQuoteByRequestId($requestId)
    {
        $collection = $this->getQuoteModel()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_sent_quotes');
        $sql = "main_table.entity_id = sent_quotes.quote_id";
        $columns = [];
        $columns[] = 'request_id';
        $columns["supplier_id"] = 'seller_id';
        $collection->getSelect()->joinLeft(['sent_quotes' => $tableName], $sql, $columns);
        $collection->addFieldToFilter("sent_quotes.request_id", $requestId);
        $collection->getSelect()->group("main_table.entity_id");

        foreach ($collection as $quote) {
            return $quote;
        }

        return $this->getQuoteModel();
    }

    /**
     * Undocumented function
     *
     * @param object $item
     * @return void
     */
    public function getQuoteProduct($item)
    {
        $productId = 0;
        $sentQuoteId = $item->getEntityId();
        $collection = $this->_productFactory->create()->getCollection();

        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote_products');
        $sql = "e.entity_id = quote_product.product_id ";
        $columns = [];
        $columns['approved_quote_id'] = 'approved_quote_id';
        $columns['quote_product_id'] = 'product_id';
        $collection->getSelect()->join(['quote_product' => $tableName], $sql, $columns);

        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_sent_quotes');
        $sql = "quote_product.approved_quote_id = sent_quotes.entity_id ";
        $columns = [];
        $columns[] = 'seller_id';
        $collection->getSelect()->join(['sent_quotes' => $tableName], $sql, $columns);

        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote_item');
        $sql = "sent_quotes.quote_item_id = quote_item.entity_id ";
        $columns = [];
        $columns['name'] = 'name';

        $collection->getSelect()->join(['quote_item' => $tableName], $sql, $columns);
        $sql = "sent_quotes.entity_id = $sentQuoteId";
        $collection->getSelect()->where($sql);

        if ($collection->getSize()) {
            foreach ($collection as $item) {
                $productId = $item->getQuoteProductId();
            }
        } else { // create product
            $collection = $this->_quotationFactory->create()->getCollection();
            $collection->addFieldToFilter("main_table.entity_id", $sentQuoteId);

            $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote_item');
            $sql = "main_table.quote_item_id = quote_item.entity_id ";
            $columns = [];
            $columns['name'] = 'name';
            // $columns['samples'] = 'samples';
            $columns['quote_id'] = 'quote_id';

            $collection->getSelect()->join(['quote_item' => $tableName], $sql, $columns);
            foreach ($collection as $quoteItem) {
                $result = $this->createQuoteProduct($quoteItem);
                if (!$result['error']) {
                    $productId = $result['product_id'];
                    $b2bProduct = $this->_b2bProductFactory->create();
                    $data = [];
                    $data["quote_id"] = $quoteItem->getQuoteId();
                    $data["approved_quote_id"] = $quoteItem->getEntityId();
                    $data["product_id"] = $productId;
                    $b2bProduct->setData($data)->save();
                }
                break;
            }
        }

        return $this->_productFactory->create()->load($productId);
    }

    public function isQuoteProduct($productId)
    {
        $collection = $this->_productFactory->create()->getCollection();
        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote_products');
        $sql = "e.entity_id = quote_product.product_id ";
        $columns = [];
        $columns['approved_quote_id'] = 'approved_quote_id';
        $collection->getSelect()->join(['quote_product' => $tableName], $sql, $columns);

        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_sent_quotes');
        $sql = "quote_product.approved_quote_id = sent_quotes.entity_id ";
        $columns = [];
        $columns[] = 'seller_id';
        $collection->getSelect()->join(['sent_quotes' => $tableName], $sql, $columns);

        $tableName = $this->_resource->getTableName('wk_b2b_requestforquote_quote_item');
        $sql = "sent_quotes.quote_item_id = quote_item.entity_id ";
        $columns = [];
        $columns['name'] = 'name';

        $collection->getSelect()->join(['quote_item' => $tableName], $sql, $columns);
        $sql = "quote_product.product_id = $productId";
        $collection->getSelect()->where($sql);

        if ($collection->getSize()) {
            return true;
        }

        return false;
    }

    public function quoteProductStatus()
    {
        return null;
    }

    /**
     * Create and check the duplicacy of url
     *
     * @param String $url
     * @param String $mainUrl
     * @param integer $i
     * @return String
     */
    public function createUrl($url, $mainUrl, $i = 0)
    {
        $urlRewriteCollection = $this->_urlRewrite->create();
        $urlRewriteCollection->addFieldToFilter('entity_type', ['eq' => 'product'])
                             ->addFieldToFilter('store_id', ['eq' => 1])
                             ->addFieldToFilter('request_path', ['eq' => $url.".html"]);
        if ($urlRewriteCollection->getSize() == 0) {
            return $url;
        }
        $url = $mainUrl;
        $i++;
        $url = $url."-".$i;
        $url = $this->createUrl($url, $mainUrl, $i);
        return $url;
    }

    public function allowProductInCart($product)
    {
        if ($this->isQuoteProduct($product->getId())) {
            return true;
        }

        return false;
    }

    public function setFilter($key, $value)
    {
        $this->_session->setData($key, $value);
    }

    public function getFilter($key)
    {
        $this->_session->getData($key);
    }

    public function getSupplierFilterProductName()
    {
        return $this->_session->getData("supplier_filter_product_name");
    }

    public function getSupplierFilterCustomerName()
    {
        return $this->_session->getData("supplier_filter_customer_name");
    }

    public function getSupplierFilterCreatedAfter()
    {
        return $this->_session->getData("supplier_filter_created_after");
    }

    public function getSupplierFilterCreatedBefore()
    {
        return $this->_session->getData("supplier_filter_created_before");
    }

    public function setQuoteFilters()
    {
        $productName = $this->_request->getParam("product_name");
        $this->setFilter("supplier_filter_product_name", $productName);
        $customerName = $this->_request->getParam("customer_name");
        $this->setFilter("supplier_filter_customer_name", $customerName);
        $createdAfter = $this->_request->getParam("created_after");
        $this->setFilter("supplier_filter_created_after", $createdAfter);
        $createdBefore = $this->_request->getParam("created_before");
        $this->setFilter("supplier_filter_created_before", $createdBefore);
    }

    public function hasAppliedFilters()
    {
        $filters = [];
        $filters[] = "supplier_filter_product_name";
        $filters[] = "supplier_filter_customer_name";
        $filters[] = "supplier_filter_created_after";
        $filters[] = "supplier_filter_created_before";
        foreach ($filters as $filter) {
            if ($this->_session->getData($filter) != "") {
                return true;
            }
        }
        return false;
    }

    public function getQuoteCategories()
    {
        $result = [];
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $objectManager->create(\Magento\Catalog\Model\Category::class)
        ->getCollection();
        $collection->addAttributeToSelect("name");

        foreach ($collection as $category) {
            $id = $category->getEntityId();
            $result[$id] = $category->getName();
        }

        return $result;
    }

    public function getCategoriesFromItem($quoteItem)
    {
        $result = [];
        $categories = $quoteItem->getCategories();
        if ($categories != "") {
            if (strpos($categories, ",") !== false) {
                $categories = explode(",", $categories);
            } else {
                $categories = [$categories];
            }

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $collection = $objectManager->create(\Magento\Catalog\Model\Category::class)
            ->getCollection();
            $collection->addFieldToFilter("entity_id", ["in" => $categories]);
            $collection->addAttributeToSelect("name");

            foreach ($collection as $category) {
                $id = $category->getEntityId();
                $result[$id] = $category->getName();
            }
        }

        return $result;
    }

    public function getSellerListByCategories($categories)
    {
        $result = [];
        if ($categories) {
            $collection = $this->_objectManager->create(\Webkul\B2BMarketplace\Model\Category::class)
            ->getCollection();
            $collection->addFieldToFilter("category_id", ["in" => $categories]);
            $collection->addFieldToSelect("supplier_id");
            $tableName = $this->_resource->getTableName('customer_entity');
            $sql = "main_table.supplier_id = customer.entity_id ";
            $columns = [];
            $columns["firstname"] = "firstname";
            $columns["lastname"] = "lastname";
            $collection->getSelect()->joinLeft(['customer' => $tableName], $sql, $columns);
            $collection->getSelect()->group("main_table.supplier_id");
            foreach ($collection as $item) {
                $result[] = $item->getSupplierId();
            }
        } else {
            $collection = $this->_objectManager->create(\Webkul\Marketplace\Model\Seller::class)
            ->getCollection();
            $collection->addFieldToFilter("is_seller", 1);
            $collection->addFieldToSelect("seller_id");
            $tableName = $this->_resource->getTableName('customer_entity');
            $sql = "main_table.seller_id = customer.entity_id ";
            $columns = [];
            $columns["firstname"] = "firstname";
            $columns["lastname"] = "lastname";
            $collection->getSelect()->joinLeft(['customer' => $tableName], $sql, $columns);
            $collection->getSelect()->group("main_table.seller_id");
            foreach ($collection as $item) {
                $result[] = $item->getSellerId();
            }
        }

        return $result;
    }

    public function generateBuyingLead($itemIds)
    {
        $collection = $this->_itemFactory->create()->getCollection();
        $collection->addFieldToFilter("entity_id", ["in" => $itemIds]);
        $collection->addFieldToFilter("buying_lead_status", ["eq" => 1]);
        $data = [];
        foreach ($collection as $quoteItem) {
            $quoteId = $quoteItem->getQuoteId();
            $quoteItemId = $quoteItem->getEntityId();
            $categories = $quoteItem->getCategories();
            if ($categories != "") {
                if (strpos($categories, ",") !== false) {
                    $categories = explode(",", $categories);
                } else {
                    $categories = [$categories];
                }
            }
            $supplierIds = $this->getSellerListByCategories($categories);
            if (count($supplierIds) > 0) {
                foreach ($supplierIds as $supplierId) {
                    $info = [];
                    $info = ["quote_id" => $quoteId, "quote_item_id" => $quoteItemId, "seller_id" => $supplierId];
                    $data[] = $info;
                }
                $quoteItem->addData(["buying_lead_status" => 2])->setId($quoteItemId)->save();
            }
        }

        try {
            $connection = $this->_objectManager
            ->create(\Magento\Framework\Module\ModuleResource::class)->getConnection();
            $connection->beginTransaction();
            $connection->insertMultiple(
                $this->_resource->getTableName('wk_b2b_requestforquote_buying_leads'),
                $data
            );
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollBack();
        }
    }

    public function getCustomerAllQuoteStatus()
    {
        $statusArr[self::QUOTE_STATUS_WAITING] = __('New');
        $statusArr[self::QUOTE_STATUS_PENDING] = __('Pending');
        $statusArr[self::QUOTE_STATUS_PROCESSING] = __('Processing');
        $statusArr[self::QUOTE_STATUS_ANSWERED] = __('Answered');
        $statusArr[self::QUOTE_STATUS_APPROVED] = __('Approved');
        $statusArr[self::QUOTE_STATUS_COMPLETE] = __('Complete');
        $statusArr[self::QUOTE_STATUS_CANCELED] = __('Canceled');
        $statusArr[self::QUOTE_STATUS_REJECTED] = __('Rejected');
        $statusArr[self::QUOTE_STATUS_SENT_TO_SUPPLIERS] = __('Sent To Suppliers');
        return $statusArr;
    }

    public function getQuoteStatusArr()
    {
        $quoteStatusArr = [];
        $quoteStatusArr["new"] = self::QUOTE_STATUS_WAITING;
        $quoteStatusArr["pending"] = self::QUOTE_STATUS_PENDING;
        $quoteStatusArr["answered"] = self::QUOTE_STATUS_ANSWERED;
        $quoteStatusArr["rejected"] = self::QUOTE_STATUS_REJECTED;
        $quoteStatusArr["approved"] = self::QUOTE_STATUS_APPROVED;
        
        return $quoteStatusArr;
    }

    public function setQuotationStatus($requestId, $itemId, $status, $customerStatus = 0)
    {
        $collection = $this->_quotationFactory->create()->getCollection()
            ->addFieldToFilter("request_id", $requestId)
            ->addFieldToFilter("quote_item_id", $itemId);
        foreach ($collection as $value) {
            $value->setStatus($status);
            if ($customerStatus) {
                $value->setCustomerStatus($customerStatus);
            }
            $value->save();
        }
    }

    public function getSampleRequiredLabel($isSampleRequired = 0)
    {
        if ($isSampleRequired) {
            return __('Yes');
        } else {
            return __('No');
        }
    }

    /**
     * Get form key
     *
     * @return string
     */
    public function getFormKey()
    {
        return $this->formKey->getFormKey();
    }

    /**
     * Check if this is valid supplie quote
     *
     * @param int $quoteId
     * @param int $itemId
     * @return bool
     */
    public function isValidSupplierQuoteItem($quoteId, $itemId)
    {
        $supplierId = $this->getSupplierId();
        $collection = $this->getAllQuoteItems();
        $collection->addFieldToFilter("main_table.entity_id", $itemId);
        $collection->addFieldToFilter("seller_id", $supplierId);
        $collection->addFieldToFilter("quote_id", $quoteId);

        if ($collection->getSize()) {
            return true;
        } else {
            // If RFQ is a buying lead.
            $collection = $this->getAllSentQuotes($itemId);
            $collection->addFieldToFilter("quote_id", $quoteId);
            $collection->addFieldToFilter("seller_id", $supplierId);
            if ($collection->getSize()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if this is valid supplie to sent quote
     *
     * @param int $quoteId
     * @param int $itemId
     * @return bool
     */
    public function isValidSupplierToQuoteItem($quoteId, $itemId)
    {
        $supplierId = $this->getSupplierId();
        $collection = $this->getAllQuoteItems();
        $collection->addFieldToFilter("main_table.entity_id", $itemId);
        $collection->addFieldToFilter("seller_id", $supplierId);
        $collection->addFieldToFilter("quote_id", $quoteId);

        if ($collection->getSize()) {
            return true;
        } else {
            // If RFQ is a buying lead.
            $collection = $this->_leadsFactory->create()
                ->getCollection()
                ->addFieldToSelect('*')
                ->addFieldToFilter('main_table.seller_id', $supplierId);
            $collection->getBuyingLeadsCollection();
            $collection->addFieldToFilter("main_table.quote_id", $quoteId);
            $collection->addFieldToFilter("main_table.quote_item_id", $itemId);
            if ($collection->getSize()) {
                return true;
            } else {
                // If RFQ is already sent for a buying lead.
                $collection = $this->getAllSentQuotes($itemId);
                $collection->addFieldToFilter("quote_id", $quoteId);
                $collection->addFieldToFilter("seller_id", $supplierId);
                if ($collection->getSize()) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * getCurrentCurrencyRate
     *
     * @return float
     */
    public function getCurrentCurrencyRate()
    {
        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyCode = $this->_mpHelper->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->_mpHelper->getBaseCurrencyCode();
        $allowedCurrencies = $this->_mpHelper->getConfigAllowCurrencies();
        $rates = $this->_mpHelper->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (!isset($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        } elseif (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }

        return $rates[$currentCurrencyCode];
    }

    /**
     * getBaseCurrencyPrice
     *
     * @param float $basePrice
     * @return float
     */
    public function getBaseCurrencyPrice($price)
    {
        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyRate = (float) $this->getCurrentCurrencyRate();
        $price = (float) $price;
        $price = $price/$currentCurrencyRate;

        return $price;
    }

    /**
     * getCurrentCurrencyPrice
     *
     * @param float $currencyRate
     * @param float $basePrice
     * @return float
     */
    public function getCurrentCurrencyPrice($currencyRate, $basePrice)
    {
        if (!$currencyRate) {
            $currencyRate = 1;
        }
        return $basePrice * $currencyRate;
    }
}
