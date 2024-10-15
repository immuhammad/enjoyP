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

namespace Webkul\B2BMarketplace\Block\Customer\Quote;

use Webkul\B2BMarketplace\Model\ResourceModel\Quotation\CollectionFactory;
use Webkul\B2BMarketplace\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Webkul\B2BMarketplace\Model\QuoteFactory;
use Webkul\B2BMarketplace\Model\ItemFactory;
use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class View extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ItemCollectionFactory
     */
    private $itemCollectionFactory;

    /**
     * @var QuoteFactory
     */
    private $quoteFactory;

    /**
     * @var ItemFactory
     */
    private $itemFactory;

    /**
     * @var \Webkul\B2BMarketplace\Model\Leads
     */
    private $customerQuoteList;

    /**
     * @var QuoteManagementInterface
     */
    private $quoteManagement;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param QuoteFactory $quoteFactory
     * @param ItemFactory $itemFactory
     * @param B2bQuoteHelper $b2bQuoteHelper
     * @param QuoteManagementInterface $quoteManagement
     * @param array $data [optional]
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        CollectionFactory $collectionFactory,
        ItemCollectionFactory $itemCollectionFactory,
        QuoteFactory $quoteFactory,
        ItemFactory $itemFactory,
        B2bQuoteHelper $b2bQuoteHelper,
        QuoteManagementInterface $quoteManagement,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->collectionFactory = $collectionFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->quoteFactory = $quoteFactory;
        $this->itemFactory = $itemFactory;
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        $this->quoteManagement = $quoteManagement;
        $this->helperFactory = $helperFactory;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getCustomerQuoteList()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'wk_b2b_customer_quote.pager'
            )
            ->setCollection(
                $this->getCustomerQuoteList()
            );
            $this->setChild('pager', $pager);
            $this->getCustomerQuoteList()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * getCurrentQuoteItemName
     *
     * @param int $id
     * @return string
     */
    public function getCurrentQuoteItemName($id)
    {
        return $this->itemFactory->create()->load($id)->getName();
    }

    public function getCustomerQuoteList()
    {
        if (!$this->customerQuoteList) {
            $quoteId = $this->getRequest()->getParam('id');
            $itemId = $this->getRequest()->getParam('item_id');
            $collection = $this->collectionFactory->create()
                ->addFieldToSelect(
                    '*'
                )->addFieldToFilter('main_table.quote_id', $quoteId)
                ->addFieldToFilter('main_table.quote_item_id', $itemId);
            $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());

            $quoteStatusArr = $this->_b2bQuoteHelper->getQuoteStatusArr();
            $currentStatus = 0;
            if ($this->getRequest()->getParam('status')) {
                $currentStatusKey = $this->getRequest()->getParam('status');
                if (isset($quoteStatusArr[$currentStatusKey])) {
                    $currentStatus = $quoteStatusArr[$currentStatusKey];
                }
            }
            $collection->addFieldToFilter('main_table.customer_status', $currentStatus);
            $this->customerQuoteList = $collection;
        }
        return $this->customerQuoteList;
    }

    public function getQuoteWithItems($type = B2bQuoteHelper::TYPE_BUYER)
    {
        return $this->_b2bQuoteHelper->getQuoteWithItems($type);
    }

    public function getCurrentTab()
    {
        $paramData = $this->getRequest()->getParams();
        $currentTab = 'new';
        if (isset($paramData['tab'])) {
            if ($paramData['tab'] == 'pending') {
                $currentTab = $paramData['tab'];
            } elseif ($paramData['tab'] == 'answered') {
                $currentTab = $paramData['tab'];
            } elseif ($paramData['tab'] == 'approved') {
                $currentTab = $paramData['tab'];
            } elseif ($paramData['tab'] == 'rejected') {
                $currentTab = $paramData['tab'];
            }
        }
        return $currentTab;
    }

    public function getTabs()
    {
        $tabs = [];
        $customerId = $this->_b2bQuoteHelper->getCustomerId();
        $quoteId = $this->getRequest()->getParam('id');
        $itemId = $this->getRequest()->getParam('item_id');
        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.quote_id', $quoteId)
            ->addFieldToFilter('main_table.quote_item_id', $itemId);
        $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());
        $collection->addFieldToFilter("main_table.customer_status", B2bQuoteHelper::QUOTE_STATUS_WAITING);
        $count = $collection->getSize();
        $tabs["new"] = ["count" => $count, "label" => "New"];

        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.quote_id', $quoteId)
            ->addFieldToFilter('main_table.quote_item_id', $itemId);
        $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());
        $collection->addFieldToFilter("main_table.customer_status", B2bQuoteHelper::QUOTE_STATUS_PENDING);
        $count = $collection->getSize();
        $tabs["pending"] = ["count" => $count, "label" => "Pending"];

        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.quote_id', $quoteId)
            ->addFieldToFilter('main_table.quote_item_id', $itemId);
        $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());
        $collection->addFieldToFilter("main_table.customer_status", B2bQuoteHelper::QUOTE_STATUS_ANSWERED);
        $count = $collection->getSize();
        $tabs["answered"] = ["count" => $count, "label" => "Answered"];

        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.quote_id', $quoteId)
            ->addFieldToFilter('main_table.quote_item_id', $itemId);
        $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());
        $collection->addFieldToFilter("main_table.customer_status", B2bQuoteHelper::QUOTE_STATUS_REJECTED);
        $count = $collection->getSize();
        $tabs["rejected"] = ["count" => $count, "label" => "Rejected"];

        $collection = $this->collectionFactory->create()
            ->addFieldToSelect(
                '*'
            )->addFieldToFilter('main_table.quote_id', $quoteId)
            ->addFieldToFilter('main_table.quote_item_id', $itemId);
        $collection->getQuoteCollectionByCustomerId($this->_b2bQuoteHelper->getCustomerId());
        $collection->addFieldToFilter("main_table.customer_status", B2bQuoteHelper::QUOTE_STATUS_APPROVED);
        $count = $collection->getSize();
        $tabs["approved"] = ["count" => $count, "label" => "Approved"];
        
        return $tabs;
    }

    public function getQuoteInfo()
    {
        $quoteId = (int) $this->getRequest()->getParam('id');
        return $this->quoteFactory->create()->load($quoteId);
    }

    public function getAllRequestedQuoteItems()
    {
        $quoteId = (int) $this->getRequest()->getParam('id');
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId);
        return $collection;
    }

    /**
     * Returns collection of quote attachments.
     *
     * @param int $quoteId
     * @return array|\Webkul\B2BMarketplace\Model\ResourceModel\QuoteAttachment\Collection
     */
    public function getQuoteAttachments($quoteId)
    {
        if ($quoteId) {
            return $this->quoteManagement->getQuoteAttachments($quoteId);
        }
        return [];
    }

    /**
     * Returns collection of quote item attachments.
     *
     * @param int $itemId
     * @return array|\Webkul\B2BMarketplace\Model\ResourceModel\ItemAttachment\Collection
     */
    public function getQuoteItemAttachments($itemId)
    {
        if ($itemId) {
            return $this->quoteManagement->getQuoteItemAttachments($itemId);
        }
        return [];
    }

    /**
     * Returns attachment URL.
     *
     * @param int $attachmentId
     * @return string
     */
    public function getAttachmentUrl($attachmentId)
    {
        return $this->getUrl('b2bmarketplace/customer_quote_attachment/download', ['attachmentId' => $attachmentId]);
    }

    /**
     * Returns item attachment URL.
     *
     * @param int $quoteId
     * @param int $attachmentId
     * @return string
     */
    public function getItemAttachmentUrl($quoteId, $attachmentId)
    {
        return $this->getUrl(
            'b2bmarketplace/customer_quote_item_attachment/download',
            [
                'quoteId' => $quoteId,
                'attachmentId' => $attachmentId
            ]
        );
    }

    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
