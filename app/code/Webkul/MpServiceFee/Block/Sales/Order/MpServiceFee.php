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
namespace Webkul\MpServiceFee\Block\Sales\Order;

use Magento\Sales\Model\Order;

class MpServiceFee extends \Magento\Framework\View\Element\Template
{
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
        $this->_source = $parent->getSource();
        $title = $this->_order->getServiceTitle();
        $store = $this->getStore();
        if ($this->_order->getCurrentCurrencyServiceFees() != null && $this->_order->getServiceFees() != 0) {
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'servicefee',
                    'strong' => false,
                    'value' => $this->_order->getCurrentCurrencyServiceFees(),
                    'base_value' => $this->_order->getServiceFees(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($customAmount, 'servicefee');

            $refunded = new \Magento\Framework\DataObject(
                [
                    'code' => 'refunded',
                    'strong' => true,
                    'value' => $this->getSource()->getTotalOfflineRefunded(),
                    'base_value' => $this->getSource()->getBaseTotalRefunded(),
                    'label' => __('Total Refunded'),
                    'area' => 'footer',
                ]
            );
            $parent->addTotal($refunded, 'refunded');

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
     * Get Order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Get Label properties
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
