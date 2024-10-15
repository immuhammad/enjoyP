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
namespace Webkul\MpServiceFee\Model\Sales\Pdf;

class MpServiceFee extends \Magento\Sales\Model\Order\Pdf\Total\DefaultTotal
{

    /**
     * Class constructor
     *
     * @param \Magento\Tax\Helper\Data $taxHelper
     * @param \Magento\Tax\Model\Calculation $taxCalculation
     * @param \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @param array $data
     */
    public function __construct(
        \Magento\Tax\Helper\Data $taxHelper,
        \Magento\Tax\Model\Calculation $taxCalculation,
        \Magento\Tax\Model\ResourceModel\Sales\Order\Tax\CollectionFactory $ordersFactory,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Sales\Model\Order\Invoice $invoice,
        array $data = []
    ) {
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_taxOrdersFactory = $ordersFactory;
        $this->request = $request;
        $this->invoice = $invoice;
        parent::__construct(
            $taxHelper,
            $taxCalculation,
            $ordersFactory,
            $data
        );
    }
    
    /**
     * Get totals to be displayed in pdf
     *
     * @return array
     */
    public function getTotalsForDisplay()
    {
        $inviceId = $this->request->getParam("invoice_id");
        $invoice = "";
        if ($inviceId > 0) {
            $invoice = $this->invoice->load($inviceId);
        }
        $amount = $this->getOrder()->formatPriceTxt($this->getOrder()->getServiceFees());

        $order = $this->getOrder();
        
        $value = 0;
        $baseValue = 0;
        $titleStr = [];
        if ($inviceId > 0) {
            $_items = $invoice->getAllItems();
            $orderItems = $order->getAllItems();
            foreach ($_items as $item) {
                foreach ($orderItems as $orderItem) {
                    if ($orderItem->getProductId() == $item->getProductId()) {
                        $titleStr[$orderItem->getServiceTitleList()] = $orderItem->getServiceTitleList();
                        $value += $orderItem->getServiceFees();
                        $baseValue += $orderItem->getCurrentCurrencyServiceFees();
                    }
                }
            }
            $amount = $value;
        }
        if ($this->getAmountPrefix()) {
            $amount = $this->getAmountPrefix() . $amount;
        }

        $title = __($this->getOrder()->getServiceTitle());
        if ($inviceId > 0) {
            $title = "Service Fee (".implode(",", $titleStr)." )";
        }
        if ($this->getTitleSourceField()) {
            $label = $title . ' (' . $this->getTitleDescription() . '):';
        } else {
            $label = $title . ':';
        }

        $fontSize = $this->getFontSize() ? $this->getFontSize() : 7;
        $total = ['amount' => $amount, 'label' => $label, 'font_size' => $fontSize];
        if ($orderItem->getServiceFees() != 0) {
            return [$total];
        }
        return [];
    }
}
