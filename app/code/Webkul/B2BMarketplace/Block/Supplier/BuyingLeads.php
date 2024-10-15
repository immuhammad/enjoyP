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

use Webkul\Marketplace\Model\ResourceModel\Product;
use Magento\Framework\Pricing\Helper\Data;
use Webkul\B2BMarketplace\Model\ResourceModel\Leads\CollectionFactory;

class BuyingLeads extends \Magento\Framework\View\Element\Template
{
    /**
     * \Webkul\B2BMarketplace\Helper\MpHelper
     */
    protected $mpHelper;

    /**
     * \Webkul\B2BMarketplace\Helper\Quote
     */
    protected $b2bQuoteHelper;

    /**
     * CollectionFactory
     */
    protected $collectionFactory;

    /**
     * \Webkul\B2BMarketplace\Model\Leads
     */
    protected $leadsCollection;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $helperFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Webkul\B2BMarketplace\Helper\MpHelper $mpHelper,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
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
        if ($this->getBuyingLeadsCollection()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mpquotesystem.pager'
            )
            ->setCollection(
                $this->getBuyingLeadsCollection()
            );
            $this->setChild('pager', $pager);
            $this->getBuyingLeadsCollection()->load();
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

    public function getBuyingLeadsCollection()
    {
        $sellerId = $this->mpHelper->getCustomerId();
        if (!$this->leadsCollection) {
            $collection = $this->collectionFactory->create()
                ->addFieldToSelect('*')
                ->addFieldToFilter('main_table.seller_id', $sellerId);
            $collection->getBuyingLeadsCollection();

            $this->leadsCollection = $collection;
        }
        return $this->leadsCollection;
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
