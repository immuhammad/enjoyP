<?php
/**
 * Webkul Affiliate User Pay Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\Affiliate\Model\PaymentFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Email as HelperEmail;

class Pay extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Webkul\Affiliate\Model\PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var \Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var Webkul\Affiliate\Helper\Email
     */
    private $helperEmail;

    /**
     * @param Context              $context
     * @param JsonFactory          $resultJsonFactory
     * @param ScopeConfigInterface $scopeConfig,
     * @param PaymentFactory       $paymentFactory,
     * @param UserBalanceFactory   $userBalanceFactory,
     * @param HelperEmail          $helperEmail
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        ScopeConfigInterface $scopeConfig,
        PaymentFactory $paymentFactory,
        UserBalanceFactory $userBalance,
        HelperEmail $helperEmail
    ) {
    
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->paymentFactory = $paymentFactory;
        $this->scopeConfig = $scopeConfig;
        $this->userBalance = $userBalance;
        $this->helperEmail = $helperEmail;
        $this->_messageManager = $context->getMessageManager();
    }

    /**
     * Affiliate User pay page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        if ($this->getRequest()->isPost()) {
            $data = $this->getRequest()->getPostValue();
            $minBal = (float)$this->scopeConfig->getValue('affiliate/general/min_pay_bal');
            $balanceRecord = $this->userBalance->create()->getCollection()
                                                ->addFieldToFilter('aff_customer_id', $data['aff_customer_id']);
            foreach ($balanceRecord as $balRecord) {
                $balanceAmount = (float)$balRecord->getBalanceAmount();
                if (is_numeric($data['transaction_amount']) && ($minBal <= $balanceAmount)
                    && ($balanceAmount >= (float)$data['transaction_amount'])) {
                    $balAmt = $balanceAmount - (float)$data['transaction_amount'];
                    $balRecord->setBalanceAmount($balAmt);
                    $balRecord->setPayNotify(0);
                    $this->_saveObj($balRecord);

                    $tmpPayment = $this->paymentFactory->create();
                    $tmpPayment->setData($data);
                    $this->_saveObj($tmpPayment);
                    $this->_messageManager->addSuccess('Transaction detail saved successfully.');
                    $responce = ['status' => true, 'msg' => __('Transaction detail saved successfully.')];

                    /** send payment credited in bank mail notification to Affiliate User*/
                    $mailData = [
                        'email_content' => __('Your affiliate user account balance credited in your bank account'),
                        'email_subject' => __('Payment credited notification')
                    ];
                    $this->helperEmail->mailToAffPaymentCreditedNotify($data['aff_customer_id'], $mailData);
                } else {
                    $responce = [
                        'status' => false,
                        'msg' => __('Invalid amount or user balance is less than minimum payout')
                    ];
                }
            }
        } else {
            $responce = ['status' => false, 'msg' => __('invalid request')];
        }
        return $this->resultJsonFactory->create()->setData($responce);
    }

    /**
     * Check Affiliate Pay To User  Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_user');
    }

    /**
     * save object.
     *
     * @return object
     */
    private function _saveObj($object)
    {
        $object->save();
    }
}
