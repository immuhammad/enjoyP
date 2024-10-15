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
namespace Webkul\MpRmaSystem\Block\Seller;

use Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory;

class Allrma extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eav;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var CollectionFactory
     */
    protected $detailsCollection;

    /**
     * @var Webkul\MpRmaSystem\Model\ResourceModel\Details\Collection
     */
    protected $allrma;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eav
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CollectionFactory $detailsCollection
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eav,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $detailsCollection,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        array $data = []
    ) {
        $this->resource          = $resource;
        $this->eav               = $eav;
        $this->customerSession   = $customerSession;
        $this->detailsCollection = $detailsCollection;
        $this->mpRmaHelper       = $mpRmaHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set Page Title
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('Marketplace RMA'));
    }

    /**
     * Get Allrma
     *
     * @return \Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory
     */
    public function getAllRma()
    {
        if (!($sellerId = $this->mpRmaHelper->getSellerId())) {
            return false;
        }

        if (!$this->allrma) {
            $collection = $this->detailsCollection
                                ->create()
                                ->addFieldToFilter('seller_id', $sellerId);
            $this->allrma = $collection;
        }

        $this->applyFilter();
        $type = \Webkul\MpRmaSystem\Helper\Data::TYPE_SELLER;
        $sortingOrder = $this->mpRmaHelper->getSortingOrder($type);
        $sortingField = $this->mpRmaHelper->getSortingField($type);
        $this->allrma->setOrder($sortingField, $sortingOrder);
        return $this->allrma;
    }

    /**
     * Prepare
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllRma()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mprmasystem.allrma.list.pager'
            )->setCollection(
                $this->getAllRma()
            );
            $this->setChild('pager', $pager);
            $this->getAllRma()->load();
        }

        return $this;
    }

    /**
     * Get pager element
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Using for apply filter
     *
     * @return void
     */
    public function applyFilter()
    {
        $type = \Webkul\MpRmaSystem\Helper\Data::TYPE_SELLER;
        $this->allrma = $this->mpRmaHelper->applyFilter($this->allrma, $type);
    }

    /**
     * Using for access Rma Helper
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function helper()
    {
        return $this->mpRmaHelper;
    }
}
