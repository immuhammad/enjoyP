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

namespace Webkul\B2BMarketplace\Block\Supplier\Quote;

use Webkul\B2BMarketplace\Model\ResourceModel\Item\CollectionFactory as ItemCollectionFactory;
use Webkul\B2BMarketplace\Api\QuoteManagementInterface;

class Request extends \Magento\Framework\View\Element\Template
{
    /**
     * \Webkul\B2BMarketplace\Helper\Quote
     */
    protected $b2bQuoteHelper;

    /**
     * \Webkul\B2BMarketplace\Model\ItemFactory
     */
    protected $itemFactory;

    /**
     * @var QuoteManagementInterface
     */
    protected $quoteManagement;

    /**
     * ItemCollectionFactory
     */
    protected $itemCollectionFactory;

    /**
     * @var [type]
     */
    private $productMediaConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $store;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $helperFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
     * @param \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory
     * @param ItemCollectionFactory $itemCollectionFactory
     * @param QuoteManagementInterface $quoteManagement
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper,
        \Webkul\B2BMarketplace\Model\ItemFactory $itemFactory,
        \Webkul\B2BMarketplace\Model\ItemAttachment $itemAttachment,
        ItemCollectionFactory $itemCollectionFactory,
        \Magento\Catalog\Model\Product\Media\Config $productMediaConfig,
        \Magento\Store\Model\StoreManagerInterface $store,
        QuoteManagementInterface $quoteManagement,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        \Magento\Framework\UrlInterface $urlInterface,
        array $data = []
    ) {
        $this->b2bQuoteHelper = $b2bQuoteHelper;
        $this->itemFactory = $itemFactory;
        $this->itemCollectionFactory = $itemCollectionFactory;
        $this->itemAttachment = $itemAttachment;
        $this->store = $store;
        $this->quoteManagement = $quoteManagement;
        $this->productMediaConfig = $productMediaConfig;
        $this->helperFactory = $helperFactory;
        $this->_urlInterface = $urlInterface;
        parent::__construct($context, $data);
    }

    public function getQuote()
    {
        return $this->b2bQuoteHelper->getQuote();
    }

    public function getQuoteId()
    {
        return $this->getQuote()->getEntityId();
    }

    public function getQuoteCustomerId()
    {
        return $this->getQuote()->getCustomerId();
    }

    public function getRequestedQuoteCurrentItem()
    {
        $itemId = (int)$this->getRequest()->getParam("item_id");
        $itemData = $this->itemFactory->create()->load($itemId);
        return $itemData;
    }

    public function getAllRequestedQuoteItems()
    {
        $sellerId = $this->b2bQuoteHelper->getSupplierId();
        $quoteId = $this->getQuoteId();
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId)
            ->getAllSellerQuoteItemsBySellerId($sellerId);
        return $collection;
    }

    public function getAllRequestedQuoteItemsId()
    {
        $sellerId = $this->b2bQuoteHelper->getSupplierId();
        $quoteId = $this->getQuoteId();
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId)
            ->getAllSellerQuoteItemsBySellerId($sellerId);
        $ids = [];
        foreach ($collection as $coll) {
            $ids [] = $coll["entity_id"];
        }
        $navigate = [];
        $itemId = (int)$this->getRequest()->getParam("item_id");

        if (in_array($itemId-1, $ids)) {
            $navigate["prev"]=$this->_urlInterface->getUrl(
                "b2bmarketplace/supplier/quote_request",
                ['id' => $quoteId, 'item_id' =>$itemId-1]
            );
        } else {
            $navigate["prev"]="#";
        }
        if (in_array($itemId+1, $ids)) {
            $navigate["next"]=$this->_urlInterface->getUrl(
                "b2bmarketplace/supplier/quote_request",
                ['id' => $quoteId, 'item_id' =>$itemId+1]
            );
        } else {
            $navigate["next"]="#";
        }
        
        return $navigate;
    }

    public function getCurrentQuoteItemCount()
    {
        $sellerId = $this->b2bQuoteHelper->getSupplierId();
        $quoteId = $this->getQuoteId();
        $itemId = (int)$this->getRequest()->getParam("item_id");
        $collection = $this->itemCollectionFactory->create()
            ->addFieldToFilter('main_table.quote_id', $quoteId)
            ->getAllSellerQuoteItemsBySellerId($sellerId);
        $ids = [];
        foreach ($collection as $coll) {
            $ids [] = $coll["entity_id"];
        }
        $key = array_search($itemId, $ids);
        return $key+1;
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
     * Returns item attachment URL.
     *
     * @param int $quoteId
     * @param int $attachmentId
     * @return string
     */
    public function getItemAttachmentUrl($quoteId, $attachmentId)
    {
        $baseMediaUrl = $this->store->getStore()->getBaseUrl(
            \Magento\Framework\UrlInterface::URL_TYPE_MEDIA
        );
        $extensionUrl = $this->productMediaConfig->getBaseTmpMediaPath();
        $itemAttachment = $this->itemAttachment
                               ->getCollection()
                               ->AddFieldToFilter(
                                   'quote_item_id',
                                   ['eq' => $quoteId],
                                   'attachment_id',
                                   ['eq' => $attachmentId]
                               )
                                ->getFirstItem()
                                ->getFilePath();
        return $baseMediaUrl.$extensionUrl.$itemAttachment;
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
