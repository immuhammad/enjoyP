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
namespace Webkul\MpRmaSystem\Block\Customer;

use Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory;

class Allrma extends \Magento\Framework\View\Element\Template
{
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
    protected $rma;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param CollectionFactory $detailsCollection
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $detailsCollection,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        array $data = []
    ) {
        $this->customerSession   = $customerSession;
        $this->detailsCollection = $detailsCollection;
        $this->mpRmaHelper       = $mpRmaHelper;
        parent::__construct($context, $data);
    }

    /**
     * Contruct
     *
     * @return $this
     */
    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('RMA Panel'));
    }

    /**
     * Get RMA
     *
     * @return \Webkul\MpRmaSystem\Model\ResourceModel\Details\CollectionFactory
     */
    public function getAllRma()
    {
        $customerId = $this->customerSession->getCustomerId();
        if (!($customerId)) {
            return false;
        }

        if (!$this->rma) {
            $collection = $this->detailsCollection
                                ->create()
                                ->addFieldToFilter('customer_id', $customerId);
            $this->rma = $collection;
        }
        $this->applyFilter();
        $sortingOrder = $this->mpRmaHelper->getSortingOrder();
        $sortingField = $this->mpRmaHelper->getSortingField();
        $this->rma->setOrder($sortingField, $sortingOrder);

        return $this->rma;
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllRma()) {
            $pager = $this->getLayout()->createBlock(
                \Magento\Theme\Block\Html\Pager::class,
                'mprmasystem.rma.list.pager'
            )->setCollection(
                $this->getAllRma()
            );
            $this->setChild('pager', $pager);
            $this->getAllRma()->load();
        }

        return $this;
    }

    /**
     * Get page Html object
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Apply Filter
     *
     * @return void
     */
    public function applyFilter()
    {
        $this->rma = $this->mpRmaHelper->applyFilter($this->rma);
    }

    /**
     * This function will return json encoded data
     *
     * @param json $data
     * @return Array
     */
    public function jsonEncodeData($data)
    {
        return $this->mpRmaHelper->jsonEncodeData($data);
    }

    /**
     * Get Field Class According To Customer
     *
     * @return string
     */
    public function getSortingFieldClass()
    {
        return $this->mpRmaHelper->getSortingFieldClass();
    }

    /**
     * Get SortingOrder Class According To Customer
     *
     * @param string $type
     * @return string
     */
    public function getSortingOrderClass()
    {
        return $this->mpRmaHelper->getSortingOrderClass();
    }

    /**
     * Function for Filter Buyer using RmaId
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function getBuyerFilterRmaId()
    {
        return $this->mpRmaHelper->getBuyerFilterRmaId();
    }

    /**
     * Function for Filter Buyer usng Order Ref
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function getBuyerFilterOrderRef()
    {
        return $this->mpRmaHelper->getBuyerFilterOrderRef();
    }

    /**
     * Function for Buyer Filter Status
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function getBuyerFilterStatus()
    {
        return $this->mpRmaHelper->getBuyerFilterStatus();
    }

    /**
     * Function for filter buyer from date
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function getBuyerFilterFromDate()
    {
        return $this->mpRmaHelper->getBuyerFilterFromDate();
    }

    /**
     * Function for filter buyer to date
     *
     * @return \Webkul\MpRmaSystem\Helper\Data
     */
    public function getBuyerFilterToDate()
    {
        return $this->mpRmaHelper->getBuyerFilterToDate();
    }

    /**
     * Get All Status of RMA
     *
     * @return array
     */
    public function getAllRmaStatus()
    {
        return $this->mpRmaHelper->getAllRmaStatus();
    }

    /**
     * Get RMA Status Title
     *
     * @param int $status
     * @param int $finalStatus
     *
     * @return string
     */
    public function getRmaStatusTitle($status, $finalStatus)
    {
        return $this->mpRmaHelper->getRmaStatusTitle($status, $finalStatus);
    }

    /**
     * Get RMA details According to customer
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->mpRmaHelper->getMessage();
    }
}
