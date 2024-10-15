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

namespace Webkul\Mpquotesystem\Block\Adminhtml;

use Webkul\Mpquotesystem\Model\ResourceModel\Quoteconversation;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Webkul\Mpquotesystem\Model\QuotesFactory;
use Webkul\Mpquotesystem\Helper\Data;
use Webkul\Mpquotesystem\Model\QuoteconversationFactory;
use Magento\Catalog\Model\ProductFactory;

class EditQuotes extends \Magento\Framework\View\Element\Template
{
    /**
     * @var customerSession
     */
    protected $_customerSession;

    /**
     * @var $\Magento\Customer\Model\Customer
     */
    protected $_customerModel;

    /**
     * @var quoteCollection
     */
    protected $_quoteConversationCollection;

    /**
     * @var pricingHelper
     */
    protected $_pricingHelper;

    /**
     * @var _quotesFactory
     */
    protected $_quotesFactory;

    /**
     * @var _productFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var _quoteConversationCollectionFactory
     */
    protected $_quoteConversationCollectionFactory;

    /**
     * @var _quoteconversationFactory
     */
    protected $_quoteconversationFactory;
    
    /**
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Customer $customerModel
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Quoteconversation\CollectionFactory $conversationCollectionFactory
     * @param QuotesFactory $_quotesFactory
     * @param ProductFactory $productFactory
     * @param QuoteconversationFactory $conversationFactory
     * @param PricingHelper $pricingHelper
     * @param Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Catalog\Block\Product\Context $context,
        Quoteconversation\CollectionFactory $conversationCollectionFactory,
        QuotesFactory $_quotesFactory,
        ProductFactory $productFactory,
        QuoteconversationFactory $conversationFactory,
        PricingHelper $pricingHelper,
        Data $helper,
        array $data = []
    ) {
        $this->_customerSession = $customerSession;
        $this->_customerModel = $customerModel;
        $this->_quoteConversationCollectionFactory = $conversationCollectionFactory;
        $this->_pricingHelper = $pricingHelper;
        $this->_quotesFactory = $_quotesFactory;
        $this->_productFactory = $productFactory;
        $this->_quoteconversationFactory = $conversationFactory;
        $this->_imageHelper = $context->getImageHelper();
        $this->helper = $helper;
        parent::__construct($context, $data);
    }

    /**
     * Get Object of helper function
     *
     * @return void
     */
    public function getHelper()
    {
        return $this->helper;
    }
    
    /**
     * Prepare layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getQuoteConversationCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpquotesystem.pager'
            )
                ->setCollection(
                    $this->getQuoteConversationCollection()
                );
            $this->setChild('pager', $pager);
            $this->getQuoteConversationCollection()->load();
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
     * Customer Id by customer session.
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
    
    /**
     * Customer data by customer id.
     *
     * @param int $customerId
     *
     * @return object
     */
    public function getCustomerData($customerId)
    {
        return $this->_customerModel->load($customerId);
    }
    
    /**
     * Get Collection of quotes conversation for particular quote id.
     *
     * @return collection
     */
    public function getQuoteConversationCollection()
    {
        if (!$this->_quoteConversationCollection) {
            $quoteId = $this->getRequest()->getParam('entity_id');
            if ($quoteId != 0) {
                $collection = $this->_quoteConversationCollectionFactory
                    ->create()
                    ->addFieldToFilter('quote_id', $quoteId)
                    ->setOrder('entity_id', 'desc');
                $this->_quoteConversationCollection = $collection;
            }
        }
        return $this->_quoteConversationCollection;
    }
    
    /**
     * Get formatted price by currency.
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
     * GetQuoteData
     *
     * @param int $entityId
     * @return void
     */
    public function getQuoteData($entityId)
    {
        $quoteModel = $this->_quotesFactory->create()->load($entityId);

        return $quoteModel;
    }

    /**
     * GetProductData
     *
     * @param int $productId
     * @return void
     */
    public function getProductData($productId)
    {
        $productModel = $this->_productFactory->create()->load($productId);

        return $productModel;
    }

    /**
     * ImageHelperObj
     *
     * @return object
     */
    public function imageHelperObj()
    {
        return $this->_imageHelper;
    }

    /**
     * Get parameters
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }

    /**
     * Check whether a quote is sold or not?
     *
     * @param int $quoteStatus
     *
     * @return void
     */
    public function quoteStatusIsNotSold($quoteStatus)
    {
        if ($quoteStatus!=\Webkul\Mpquotesystem\Model\Quotes::STATUS_SOLD) {
            return true;
        }
        return false;
    }
}
