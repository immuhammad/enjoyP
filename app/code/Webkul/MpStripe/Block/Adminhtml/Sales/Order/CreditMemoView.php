<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Adminhtml\Sales\Order;

use Magento\Sales\Model\Order;

class CreditMemoView extends \Magento\Framework\View\Element\Template
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
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\View\Element\Template\Context $context,
        array $data = []
    ) {
        $this->mpHelper = $mpHelper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * GetSource function
     *
     * @return Object
     */
    public function getSource()
    {
        return $this->_source;
    }

    /**
     * DisplayFullSummary function
     *
     * @return boolean
     */
    public function displayFullSummary()
    {
        return true;
    }

    /**
     * InitTotals function
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $creditmemoId = $this->request->getParam('creditmemo_id');
        if ($creditmemoId) {
            if ($this->_order->getPayment()->getMethod() == \Webkul\MpStripe\Model\PaymentMethod::METHOD_CODE &&
            !$this->mpHelper->getConfigTaxManage()) {

                $creditmemoData = $this->_order->getCreditmemosCollection();
                $invoiceData = $this->_order->getInvoiceCollection();
                $finalInvoiceData = '';
                
                foreach ($creditmemoData as $creditmemo) {
                    foreach ($invoiceData as $invoice) {
                        if ($invoice->getId() == $creditmemo->getInvoiceId()) {
                            $finalInvoiceData = $invoice;
                        }
                    }
                    $creditmemo_id = $creditmemo->getId();
                    if ($creditmemo_id == $creditmemoId) {
                        if ($finalInvoiceData->getTaxAmount() == 0) {
                            $parent->removeTotal('tax');
                            $grandTotalNew = $creditmemo->getGrandTotal()-$creditmemo->getTaxAmount();
                            $baseGrandTotalNew = $creditmemo->getBaseGrandTotal()-$creditmemo->getBaseTaxAmount();
                            $grandTotal = new \Magento\Framework\DataObject(
                                [
                                    'code' => 'grand_total',
                                    'strong' => true,
                                    'value' => $grandTotalNew,
                                    'base_value' => $baseGrandTotalNew,
                                    'label' => __('Grand Total'),
                                ]
                            );
                            $parent->addTotal($grandTotal, 'grand_total');
                        }
                    }
                }

            }
            return $this;
        }
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
     * @return Order
     */

    /**
     * GetOrder function
     *
     * @return Order $_order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * GetLabelProperties function
     *
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }

    /**
     * GetValueProperties function
     *
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}
