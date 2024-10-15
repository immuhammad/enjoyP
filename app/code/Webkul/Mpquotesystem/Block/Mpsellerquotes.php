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

namespace Webkul\Mpquotesystem\Block;

use Webkul\Mpquotesystem\Model\ResourceModel\Quotes;
use Webkul\Marketplace\Model\ResourceModel\Product;
use Magento\Framework\Pricing\Helper\Data;

class Mpsellerquotes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var customerSession
     */
    protected $_customerSession;

    /**
     * @var quoteCollectionFacory
     */
    protected $_quoteCollectionFactory;

    /**
     * @var mpproductCollectionFacory
     */
    protected $_mpproductCollectionFacory;

    /**
     * @var quoteCollection
     */
    protected $_quoteCollection;

    /**
     * @var pricingHelper
     */
    protected $_pricingHelper;

    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Quotes\CollectionFactory $quotesCollectionFactory
     * @param Product\CollectionFactory $productCollectionFactory
     * @param Data $pricingHelper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Mpquotesystem\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        Quotes\CollectionFactory $quotesCollectionFactory,
        Product\CollectionFactory $productCollectionFactory,
        Data $pricingHelper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_quoteCollectionFactory = $quotesCollectionFactory;
        $this->_mpproductCollectionFacory = $productCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_mpHelper = $mpHelper;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }
    
    /**
     * Prepare layout
     *
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
     * GetPagerHtml
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
    
    /**
     * Customer Id by customer session
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_mpHelper->getCustomerId();
    }
    
    /**
     * Get Collection of quotes
     *
     * @return collection
     */
    public function getQuotesCollection()
    {
        if (!$this->_quoteCollection) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterQuoteId = '';
            if (isset($paramData['quote_id'])) {
                $filterQuoteId = $paramData['quote_id'] != '' ? $this->escapeHtml($paramData['quote_id']) : '';
            }
            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $this->escapeHtml($paramData['s']) : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            $productCollection = [];
            $productCollection = $this->_mpproductCollectionFacory->create()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $this->getCustomerId()]
                )
                ->addFieldToSelect('mageproduct_id');
            $collection = $this->_quoteCollectionFactory
                ->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter(
                    'product_id',
                    ['in' => $productCollection->getData()]
                );
            if ($filterStatus) {
                $collection->addFieldToFilter(
                    'status',
                    $filterStatus
                );
            }
            if ($filter) {
                $collection->addFieldToFilter(
                    'product_name',
                    ['like' => '%'.$filter.'%']
                );
            }
            if ($filterQuoteId) {
                $collection->addFieldToFilter(
                    'entity_id',
                    $filterQuoteId
                );
            }
            $collection->setOrder('created_at', 'desc');
            $this->_quoteCollection = $collection;
        }
        return $this->_quoteCollection;
    }
    
    /**
     * Get formatted price by currency
     *
     * @param string $price
     *
     * @return string
     */
    public function getFormattedPrice($price)
    {
        return $this->_pricingHelper
            ->currency($price, true, false);
    }

    /**
     * Get image url
     *
     * @param string $imageType
     *
     * @return string
     */
    public function getImageUrl($imageType)
    {
        return $this->getViewFileUrl($imageType);
    }
    
    /**
     * GetIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }
    
    /**
     * Check whether a quote is sold or not?
     *
     * @param int $quoteStatus
     *
     * @return boolean
     */
    public function quoteStatusIsNotSold($quoteStatus)
    {
        if ($quoteStatus!=\Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
            return true;
        }
        return false;
    }

    /**
     * Get object of helper class
     *
     * @return void
     */
    public function getHelper()
    {
        return $this->helper;
    }

    /**
     * Get object of Mp helper class
     *
     * @return void
     */
    public function getMpHelper()
    {
        return $this->_mpHelper;
    }
}
