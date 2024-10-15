<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpServiceFee\Block\Seller\Sales\Order;

use Magento\Sales\Model\Order;

class MpServiceFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpServiceFee\Helper\Data
     */
    protected $curHelper;

    /**
     * @var Collection
     */
    protected $orderCollection;

    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * Class constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $curHelper
     * @param \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $orderCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\MpServiceFee\Helper\Servicehelper $curHelper,
        \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory $orderCollection,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->curHelper = $curHelper;
        $this->orderCollection = $orderCollection;
        parent::__construct($context, $data);
    }

    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Returns source
     *
     * @return object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Displays summary
     *
     * @return boolean
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $quoteId = $this->_order->getQuoteId();
        $sellerIdInSession = $this->helper->getCustomerId();
        $sellerServiceFeeFromQuote = $this->curHelper->getSellerServiceFeeFromQuote($quoteId);
        $allAppliedServiceFeesInOrder = "";
        $feeTitle = [];
        foreach ($this->_order->getAllItems() as $orderItem) {
            $sellerId = $this->curHelper->getSellerId($orderItem->getProductId());
            if ($sellerId == $sellerIdInSession) {
                $feeTitle[$orderItem->getServiceTitleList()] = $orderItem->getServiceTitleList();
            }
        }
        $allAppliedServiceFeesInOrder= __("Service Fee")."(".implode(",", $feeTitle).")";
        if ($allAppliedServiceFeesInOrder == "Service Fee()") {
            $allAppliedServiceFeesInOrder= __("Service Fee");
        }
        $sellerServiceFeeFromQuote = array_values($sellerServiceFeeFromQuote);
        $this->_source = $parent->getSource();
        $title = $this->_order->getServiceTitle();
        $store = $this->getStore();
        if ($this->_order->getCurrentCurrencyServiceFees() != null) {
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'servicefee',
                    'strong' => false,
                    'value' => $sellerServiceFeeFromQuote[0]["currCurrencyServiceFeeTotal"],
                    'base_value' => $sellerServiceFeeFromQuote[0]["baseservicefees"],
                    'label' => $allAppliedServiceFeesInOrder,
                ]
            );
            $parent->addTotal($customAmount, 'servicefee');
            $serviceFee = $parent->getTotal('servicefee');
            $total = $parent->getTotal('ordered_total');
            if (!$total) {
                return $this;
            }
            $total->setValue($total->getValue() + $serviceFee->getValue());
            $total = $parent->getTotal('vendor_total');
            if (!$total) {
                return $this;
            }
            $total->setValue($total->getValue() + $serviceFee->getValue());
        }
        return $this;
    }

    /**
     * Get order store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Get value properties
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
