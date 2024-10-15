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

/**
 * Tax totals modification block. Can be used just as subblock of \Magento\Sales\Block\Order\Totals
 */
namespace Webkul\MpServiceFee\Block\Adminhtml\Sales\Order\Invoice;

class MpServiceFee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Tax\Model\Config
     */
    protected $_config;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        parent::__construct($context, $data);
    }

    /**
     * Display Summary
     *
     * @return boolean
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * Source getter function
     *
     * @return object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * Store getter function
     *
     * @return object
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }

    /**
     * Order getter function
     *
     * @return object
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * Returns label properties
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * Returns value
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }

    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $invoice = $parent->getInvoice();
        $_items = $invoice->getAllItems();
        $orderItems = $parent->getOrder()->getAllItems();
        $value = 0;
        $baseValue = 0;
        $feeTitle = [];
        foreach ($_items as $item) {
            foreach ($orderItems as $orderItem) {
                if ($orderItem->getProductId() == $item->getProductId()) {
                    $feeTitle[$orderItem->getServiceTitleList()] = $orderItem->getServiceTitleList();
                    $value += $orderItem->getServiceFees();
                    $baseValue += $orderItem->getCurrentCurrencyServiceFees();
                }
            }
        }
        if ($baseValue != 0) {
            $fee = new \Magento\Framework\DataObject(
                [
                    'code' => 'servicefee',
                    'strong' => false,
                    'value' => $value,
                    'base_value' => $baseValue,
                    'label' => __("Service Fee")."(".implode(",", $feeTitle).")",
                ]
            );
    
            $parent->addTotal($fee, 'servicefee');
        }

        return $this;
    }
}
