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
namespace Webkul\MpServiceFee\Model\Total;

class MpServiceFee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    
    /**
     * @var decimal
     */
    protected $totalFees;

    /**
     * @var decimal
     */
    protected $convertedTotalFees;

    /**
     * @var array
     */
    protected $serviceNames = [];

    /**
     * @var AttributesListFactory
     */
    protected $modelFactory;

    /**
     * @var Pricecurrency
     */
    protected $pricecurrency;

    /**
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper
     * @param \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        \Webkul\MpServiceFee\Helper\Servicehelper $serviceHelper,
        \Webkul\MpServiceFee\Model\AttributesListFactory $modelFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
    ) {
        $this->modelFactory = $modelFactory;
        $this->serviceHelper = $serviceHelper;
        $this->pricecurrency = $priceCurrency;
        $this->isEnabled = $this->serviceHelper->isModuleEnable();
    }

    /**
     * Collect grand total address amount
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     * @return $this
     */
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);
        if (!$this->isEnabled) {
            return $this;
        }
        $information = $quote->getAllVisibleItems();
        foreach ($information as $item) {
            if ($item->getProductType() == "donation") {
                return $this;
            }
        }
        if ($quote->getIsMultiShipping()) {
            if (empty($shippingAssignment->getItems())) {
                return $this;
            }
            $this->totalFees = $this->serviceHelper->getTotalFees($quote);
            $this->convertedTotalFees = $this->pricecurrency->convert($this->totalFees);
            $total->setTotalAmount('customfee', $this->convertedTotalFees);
            $total->setBaseTotalAmount('customfee', $this->totalFees);
            $quote->setServiceFees($this->totalFees);
            $quote->setCurrentCurrencyServiceFees($this->convertedTotalFees);
            $quote->save();
            return $this;
        } else {
            if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == "shipping") {
                $this->totalFees = $this->serviceHelper->getTotalFees($quote);
                $this->convertedTotalFees = $this->pricecurrency->convert($this->totalFees);
                $total->setTotalAmount('customfee', $this->convertedTotalFees);
                $total->setBaseTotalAmount('customfee', $this->totalFees);
            }
            if ($quote->getIsVirtual()) {
                if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == "billing") {
                    $this->totalFees = $this->serviceHelper->getTotalFees($quote);
                    $this->convertedTotalFees = $this->pricecurrency->convert($this->totalFees);
                    $total->setTotalAmount('customfee', $this->convertedTotalFees);
                    $total->setBaseTotalAmount('customfee', $this->totalFees);
                }
            }
            $quote->setServiceFees($this->totalFees);
            $quote->setCurrentCurrencyServiceFees($this->convertedTotalFees);
            $quote->save();
            return $this;
        }
    }

    /**
     * Clear address total field values
     *
     * @param Address\Total $total
     * @return void
     */
    protected function clearValues(Address\Total $total)
    {
        $total->setTotalAmount('subtotal', 0);
        $total->setBaseTotalAmount('subtotal', 0);
        $total->setTotalAmount('tax', 0);
        $total->setBaseTotalAmount('tax', 0);
        $total->setTotalAmount('discount_tax_compensation', 0);
        $total->setBaseTotalAmount('discount_tax_compensation', 0);
        $total->setTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setBaseTotalAmount('shipping_discount_tax_compensation', 0);
        $total->setSubtotalInclTax(0);
        $total->setBaseSubtotalInclTax(0);
    }

    /**
     * Assign subtotal amount and label to address object
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Address\Total $total
     * @return array
     */
    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        if (!$this->isEnabled) {
            return [];
        }
        if ($quote->getIsVirtual() || $this->totalFees === null) {
            $this->totalFees = $this->serviceHelper->getTotalFees($quote);
        }
        if ($this->convertedTotalFees === null) {
            $this->convertedTotalFees = $this->pricecurrency->convert($this->totalFees);
        }
        if ($this->convertedTotalFees == 0) {
            return [];
        }
        return [
            'code' => 'customfee',
            'title' => $this->getLabel(),
            'value' => $this->convertedTotalFees,
        ];
    }

    /**
     * Get Subtotal label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getLabel()
    {
        return __($this->serviceHelper->activeServiceNames());
    }
}
