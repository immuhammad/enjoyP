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
namespace Webkul\MpServiceFee\Block\Total\Invoice;

use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Model\Order;

class MpServicefee extends \Magento\Framework\View\Element\Template
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
     * Class constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Tax\Model\Config $taxConfig
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param InvoiceRepositoryInterface $invoiceRepository
     * @param \Magento\Quote\Model\QuoteFactory $quote
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Tax\Model\Config $taxConfig,
        \Webkul\MpServiceFee\Helper\Servicehelper $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        InvoiceRepositoryInterface $invoiceRepository,
        \Magento\Quote\Model\QuoteFactory $quote,
        array $data = []
    ) {
        $this->_config = $taxConfig;
        $this->invoiceRepository = $invoiceRepository;
        $this->helper = $helper;
        $this->quote = $quote;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }
    /**
     * Init totals
     *
     * @return $this
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();

        $invoiceId = $parent->getInvoice()->getId();
        $this->_order = $parent->getOrder();

        $this->_source = $parent->getSource();
        $title = $this->_order->getServiceTitle();
        $store = $this->getStore();

        $quoteId = $this->_order->getQuoteId();
        $quote = $this->quote->create()->load($quoteId);
        $items = $quote->getAllItems();
        $invoiceData = $this->invoiceRepository->get($invoiceId);

        $currentCurrencyServiceFees = 0;
        $itemsInvoice = $invoiceData->getAllItems();
        $feeTitle = [];
        foreach ($itemsInvoice as $itemInvoice) {
            foreach ($items as $item) {
                if ($itemInvoice->getProductId() == $item->getProductId()) {
                    $sellerId = $this->mpHelper->getSellerIdByProductId($this->mpHelper->getSellerIdByProductId());
                    $feeTitle[$sellerId] = $item->getServiceTitleList();
                    $currentCurrencyServiceFees += $item->getCurrentCurrencyServiceFees();
                }
            }
        }
        $title= __("Service Fee")."(".implode(",", $feeTitle).")";
        $sellerIds = [];
        foreach ($items as $item) {
            $sellerId = $this->helper->getSellerIdPerProduct($item);
            $sellerIds[$this->helper->getSellerIdPerProduct($item)] = $sellerId;
        }

        if ($currentCurrencyServiceFees != null) {
            $customAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'servicefee',
                    'strong' => false,
                    'value' => ($currentCurrencyServiceFees),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($customAmount, 'servicefee');
        }
        return $this;
    }
}
