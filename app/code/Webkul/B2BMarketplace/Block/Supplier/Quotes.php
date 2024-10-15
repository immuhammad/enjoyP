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

namespace Webkul\B2BMarketplace\Block\Supplier;

use Webkul\B2BMarketplace\Helper\Quote as B2bQuoteHelper;
use Webkul\B2BMarketplace\Model\ResourceModel\Quote\CollectionFactory;

class Quotes extends \Magento\Framework\View\Element\Template
{
    /**
     * B2bQuoteHelper
     */
    protected $b2bQuoteHelper;

    /**
     * CollectionFactory
     */
    protected $collectionFactory;

    /**
     * \Webkul\B2BMarketplace\Model\Quote
     */
    protected $_quoteCollection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $helperFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        B2bQuoteHelper $b2bQuoteHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        $this->collectionFactory = $collectionFactory;
        $this->helperFactory = $helperFactory;
        $this->messageManager = $messageManager;
        parent::__construct($context, $data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuotesCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpquotesystem.pager'
            )
            ->setCollection(
                $this->getQuotesCollection()
            );
            $this->setChild('pager', $pager);
            $this->getQuotesCollection()->load();
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

    public function getQuotesCollection()
    {
        try {
            $sellerId = $this->_b2bQuoteHelper->getSupplierId();
            if (!$this->_quoteCollection) {
                $currentStatus = 0;
                if ($this->getRequest()->getParam('status')) {
                    $currentStatusKey = $this->getRequest()->getParam('status');
                    $currentStatus = $this->getCurrentStatusKeyVal($currentStatusKey);
                }
                $collection = $this->collectionFactory->create()
                    ->addFieldToSelect('*')
                    ->getAllSellerQuoteBySellerId($sellerId, $currentStatus);
                $filterData = $this->getRequest()->getParams();
                if (isset($filterData['product_name']) && $filterData['product_name']) {
                    $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$filterData['product_name'].'%']);
                }
                if (isset($filterData['customer_name']) && $filterData['customer_name']) {
                    $collection->addFieldToFilter('customer_name', $filterData['customer_name']);
                }
                if (isset($filterData['created_after']) && $filterData['created_after']) {
                    $date = date_create($filterData['created_after']);
                    $dateFormated = date_format($date, 'Y-m-d H:i:s');
                    $collection->addFieldToFilter("main_table.created_at", ["gteq" => $dateFormated]);
                }
                if (isset($filterData['created_before']) && $filterData['created_before']) {
                    $date = date_create($filterData['created_before']);
                    $dateFormated = date_format($date, 'Y-m-d H:i:s');
                    $collection->addFieldToFilter("main_table.created_at", ["lteq" => $dateFormated]);
                }

                //$collection->addFieldToFilter('main_table.status', $currentStatus);

                if ($currentStatus == B2bQuoteHelper::QUOTE_STATUS_WAITING) {
                    $collection->addFieldToFilter('quote_item.status', $currentStatus);
                    $collection->addFieldToFilter('main_table.is_buying_lead', 0);
                } else {
                    $collection->addFieldToFilter('sent_quotes.status', $currentStatus);
                }

                $collection->setOrder('main_table.created_at');

                $this->_quoteCollection = $collection;
            }
            return $this->_quoteCollection;
            
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
    }

    public function getCurrentStatusKeyVal($currentStatusKey)
    {
        $quoteStatusArr = $this->_b2bQuoteHelper->getQuoteStatusArr();
        $currentStatus = 0;
        if ($currentStatusKey) {
            if (isset($quoteStatusArr[$currentStatusKey])) {
                $currentStatus = $quoteStatusArr[$currentStatusKey];
            }
        }
        return $currentStatus;
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
