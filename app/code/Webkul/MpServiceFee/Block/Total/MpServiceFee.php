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
namespace Webkul\MpServiceFee\Block\Total;

use Magento\Sales\Model\Order;

class MpServiceFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $source;

    /**
     * Get data (totals) source model
     *
     * @return \Magento\Framework\DataObject
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
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
        $this->_source = $parent->getSource();
        $title = $this->_order->getServiceTitle();
        $store = $this->getStore();
        if ($this->_order->getCurrentCurrencyServiceFees() != null) {
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'servicefee',
                    'strong' => false,
                    'value' => $this->_order->getCurrentCurrencyServiceFees(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($customAmount, 'servicefee');
            $grandTotal = $parent->getTotal('grand_total');
            $serviceFee = $parent->getTotal('servicefee');
        }
        return $this;
    }
}
