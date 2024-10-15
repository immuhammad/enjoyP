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
namespace Webkul\MpRmaSystem\Controller\Rma;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Webkul\MpRmaSystem\Helper\Data;

class Refund extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

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
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details
    ) {
        $this->url         = $url;
        $this->session     = $session;
        $this->mpRmaHelper = $mpRmaHelper;
        $this->details     = $details;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->url->getLoginUrl();
        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
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
        $IsCustomer = $helper->getCustmerByRmaId($rmaId);
        $stock = isset($data['back_to_stock']) ? 1 : 0;
        $doOffline = $data['do_offline'];
        $invoiceId = $data['invoice_id'];
        if ($invoiceId) {
            $myinvoiceId = explode(',', $invoiceId);
            foreach ($myinvoiceId as $invoiceId) {
                if ($invoiceId) {
                    $invoiceId = $invoiceId;
                }
            }
        }
        if (!is_numeric($data['partial_amount'])) {
            $this->messageManager->addError(__("You have enter wrong amount format"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/seller/rma',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
        }
        if (!$IsCustomer) {
            $this->messageManager->addError(__("Customer not exists"));
            return $this->resultRedirectFactory
                    ->create()
                    ->setPath(
                        '*/seller/rma',
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
                        '*/seller/rma',
                        ['id' => $rmaId, 'back' => null, '_current' => true]
                    );
    }
}
