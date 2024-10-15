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

namespace Webkul\B2BMarketplace\Block\Customer;

use Webkul\B2BMarketplace\Model\ResourceModel\Quote\CollectionFactory;

class Quotes extends \Magento\Framework\View\Element\Template
{
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

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
        $this->collectionFactory = $collectionFactory;
        $this->helperFactory = $helperFactory;
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

    /**
     * customer Id by customer session
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    public function getQuotesCollection()
    {
        if (!$this->_quoteCollection) {
            $collection = $this->collectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('customer_id', $this->getCustomerId())
                ->setOrder('entity_id', 'DESC')
                ->getAllCustomerQuote();
            $filterData = $this->getRequest()->getParams();
            if (isset($filterData['product_name']) && $filterData['product_name']) {
                $collection->addFieldToFilter('quote_item.name', ['like' => '%'.$filterData['product_name'].'%']);
            }
            if ((isset($filterData['quote_status']) && $filterData['quote_status']!=null) &&
            $filterData['quote_status'] != "All Status") {
                $collection->addFieldToFilter('quote_item.status', $filterData['quote_status']);
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
            $this->_quoteCollection = $collection;
        }
        return $this->_quoteCollection;
    }

    /**
     * getTotalSuppliers
     *
     * @param \Webkul\B2BMarketplace\Model\Quote
     * @return int
     */
    public function getTotalSuppliers($quote)
    {
        if ($quote && $quote->getBuyingLeadStatus()) {
            $collectionData = $this->collectionFactory->create()
                ->addFieldToFilter('entity_id', $quote->getId())
                ->getBuyingLeadsTotalSuppliers();
            if (isset($collectionData[0])) {
                return $collectionData[0];
            }
        }
        return __('N/A');
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
