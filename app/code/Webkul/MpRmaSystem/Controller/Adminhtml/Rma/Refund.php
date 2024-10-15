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
namespace Webkul\MpRmaSystem\Controller\Adminhtml\Rma;

use Webkul\MpRmaSystem\Helper\Data;

class Refund extends \Webkul\MpRmaSystem\Controller\Adminhtml\Rma
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @return void
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details
    ) {
        $this->mpRmaHelper  = $mpRmaHelper;
        $this->details      = $details;
        parent::__construct($context);
    }

    /**
     * Refund Action
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->mpRmaHelper;
        $data = $this->getRequest()->getParams();
        $rmaId = $data['rma_id'];
        $negative = 0;
        $totalPrice = 0;
        $partial_amount = 0;
        $productDetails = $helper->getRmaProductDetails($rmaId);
        $stock = isset($data['back_to_stock']) ? 1 : 0;
        $IsCustomer = $helper->getCustmerByRmaId($rmaId);
        $doOffline = $data['do_offline'];
        $invoiceId = $data['invoice_id'];
        if (!is_numeric($data['partial_amount'])) {
            $this->messageManager->addError(__("You have enter wrong amount format"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }

        if (!$IsCustomer) {
            $this->messageManager->addError(__("Customer not exists"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }

        if ($productDetails->getSize()) {
            foreach ($productDetails as $item) {
                $totalPrice += $helper->getItemFinalPrice($item);
            }
        }

        if ($data['payment_type'] == 2) {
            $partial_amount = str_replace(',', '', $data['partial_amount']);
            $negative = $totalPrice - $partial_amount;
        }

        $data = [
            'rma_id' => $rmaId,
            'negative' => $negative,
            'do_offline' => $doOffline,
            'invoice_id' => $invoiceId,
            'back_to_stock'=> $stock
        ];
        $result = $helper->createCreditMemo($data);
        if ($result['error']) {
            $this->messageManager->addError($result['msg']);
        } else {
            $rmaData = [
                        'status' => Data::RMA_STATUS_SOLVED,
                        'seller_status' => Data::SELLER_STATUS_SOLVED,
                        'final_status' => Data::FINAL_STATUS_SOLVED,
                        'refunded_amount' => $totalPrice - $negative,
                        'memo_id' => $result['memo_id'],
                    ];
            $this->messageManager->addSuccess($result['msg']);
            $rma = $this->details->create()->load($rmaId);
            $orderId = $rma->getOrderId();
            $rma->addData($rmaData)->setId($rmaId)->save();
            $helper->updateMpOrder($orderId, $result['memo_id']);
            $helper->sendUpdateRmaEmail($data);
            $helper->updateRmaItemQtyStatus($rmaId);
        }

        return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/rma/edit',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
    }
}
