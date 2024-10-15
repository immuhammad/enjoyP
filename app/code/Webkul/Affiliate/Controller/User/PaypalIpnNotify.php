<?php
/**
 * Webkul Affiliate User Payment Notify.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\User;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Affiliate\Model\PaymentFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;

class PaypalIpnNotify extends \Magento\Framework\App\Action\Action
{
    /**
     * @var UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var PaymentFactory
     */
    private $payment;

    /**
     * @var \Webkul\Affiliate\Logger\Logger
     */
    private $logger;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PaymentFactory $paymentFactory,
        UserBalanceFactory $userBalanceFactory,
        \Webkul\Affiliate\Logger\Logger $logger
    ) {
    
        $this->userBalance = $userBalanceFactory;
        $this->payment = $paymentFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * PaypalIpnNotify Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $wholedata = $this->getRequest()->getParams();
        $transdata=[];
        $invoice=explode('-', $wholedata['invoice']);
        if ($wholedata['payer_status']=='verified') {
            $paymtObj=$this->payment->create();
            $paymtObj->setTransactionId($wholedata['txn_id']);
            $paymtObj->setTransactionEmail($wholedata['payer_email']);
            $paymtObj->setIpnTransactionId($wholedata['ipn_track_id']);
            $paymtObj->setAffCustomerId($invoice[1]);
            $paymtObj->setTransactionAmount($wholedata['payment_gross']);
            $paymtObj->setTransactionDate($wholedata['payment_date']);
            $paymtObj->setPaymentmethod('PayPal');
            $paymtObj->setTransactionStatus($wholedata['payment_status']);
            $paymtObj->save();

            $getbalance = $this->userBalance->create()->getCollection()
                                ->addFieldToFilter('aff_customer_id', ['eq'=>$invoice[1]])
                                ->setPageSize(1)->setCurPage(1)->getFirstItem();
            if ($getbalance->getEntityId()) {
                $getbalance->setBalance(0);
                $getbalance->save();
            }
        }
    }
}
