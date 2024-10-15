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

namespace Webkul\MpRmaSystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MpRmaSystem\Helper\Data;

class RmaRefundByAdminObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @return void
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Psr\Log\LoggerInterface $logger,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details
    ) {
        $this->request      = $request;
        $this->logger       = $logger;
        $this->mpRmaHelper  = $mpRmaHelper;
        $this->details      = $details;
    }

    /**
     * Admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $negative = 0;
        $totalPrice = 0;
        $partial_amount = 0;
        $data = $this->request->getPost();
        $order = $observer->getEvent()->getOrder();
        if ($order instanceof Order == false) {
            /** @var \Magento\Sales\Model\Order\Payment $payment */
            $payment = $observer->getEvent()->getPayment();
            if ($payment instanceof \Magento\Sales\Model\Order\Payment) {
                $order = $payment->getOrder();
            }
            if ($order instanceof Order == false) {
                /** @var \Magento\Sales\Model\Creditmemo $creditmemo */
                $creditmemo = $observer->getEvent()->getCreditmemo();
                if ($creditmemo instanceof \Magento\Sales\Model\Order\Creditmemo) {
                    $order = $creditmemo->getOrder();
                }
            }
        }
        try {
            $rmaDetails = $this->mpRmaHelper->getRmaByOrderId($order->getId());
            if (!empty($rmaDetails)) {
                foreach ($rmaDetails as $rmaDetail) {
                    $rmaId = $rmaDetail->getId();
                }
                $productDetails = $this->mpRmaHelper->getRmaProductDetails($rmaId);
                if ($productDetails->getSize()) {
                    foreach ($productDetails as $item) {
                        $totalPrice += $this->mpRmaHelper->getItemFinalPrice($item);
                    }
                }
                
                $rmaData = [
                    'status' => Data::RMA_STATUS_SOLVED,
                    'seller_status' => Data::SELLER_STATUS_SOLVED,
                    'final_status' => Data::FINAL_STATUS_SOLVED,
                    'refunded_amount' => 0,
                    'memo_id' => 0,
                ];
                $rma = $this->details->create()->load($rmaId);
                $orderId = $rma->getOrderId();
                $rma->addData($rmaData)->setId($rmaId)->save();
                // $this->mpRmaHelper->manageStock($rmaId, $productDetails);
                $this->mpRmaHelper->updateRmaItemQtyStatus($rmaId);
            }
        } catch (\Exception $e) {
            $this->logger->info('Mp RMA Admin'.$e->getMessage());
        }
    }
}
