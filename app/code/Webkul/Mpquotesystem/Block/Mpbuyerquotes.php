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

class Mpbuyerquotes extends \Magento\Framework\View\Element\Template
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
     * @var \Magento\Framework\Url\EncoderInterface
     */
    protected $_urlEncoder;

    /**
     * @param \Magento\Customer\Model\Session         $customerSession
     * @param \Magento\Catalog\Block\Product\Context  $context
     * @param Quotes\CollectionFactory                $quotesCollectionFactory
     * @param Product\CollectionFactory               $productCollectionFactory
     * @param Data                                    $pricingHelper
     * @param \Magento\Framework\Url\EncoderInterface $urlEncoder
     * @param \Webkul\Mpquotesystem\Helper\Data       $helper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Block\Product\Context $context,
        Quotes\CollectionFactory $quotesCollectionFactory,
        Product\CollectionFactory $productCollectionFactory,
        Data $pricingHelper,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_quoteCollectionFactory = $quotesCollectionFactory;
        $this->_mpproductCollectionFacory = $productCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_urlEncoder = $urlEncoder;
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
        return $this->_customerSession->getCustomerId();
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
            $collection = $this->_quoteCollectionFactory
                ->create()->addFieldToSelect('*')
                ->addFieldToFilter(
                    'customer_id',
                    $this->getCustomerId()
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
            $collection->setOrder('entity_id', 'DESC');
            $this->_quoteCollection = $collection;
        }
        return $this->_quoteCollection;
    }
    
    /**
     * Get formatted price by currency
     *
     * @param string $price
     *
     * @return string $price
     */

    public function getFormattedPrice($price)
    {
        return $this->_pricingHelper
            ->currency($price, true, false);
    }
    
    /**
     * Use to get current url.
     *
     * @return string
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }

    /**
     * Get ajax Url
     *
     * @return string
     */
    public function getajaxUrl()
    {
        return $this->_urlEncoder->encode(
            $this->_urlBuilder->getUrl(
                '*/*/*',
                ['_use_rewrite' => true, '_current' => true]
            )
        );
    }

    /**
     * Get Image url
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
     * Get attachment path
     *
     * @param string $path
     * @return string
     */
    public function getAttchment($path)
    {
        return $this->helper->getAttachFullUrl($path);
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
}
