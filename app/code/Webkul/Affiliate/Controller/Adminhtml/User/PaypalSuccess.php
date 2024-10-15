<?php
/**
 * Webkul Affiliate User PaypalSuccess
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Affiliate\Model\PaymentFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Logger\Logger;
use Webkul\Affiliate\Helper\Email as HelperEmail;

class PaypalSuccess extends \Magento\Backend\App\Action
{
     /**
      * @var Logger
      */
    private $logger;

    /**
     * @var PaymentFactory
     */
    private $paymentFactory;

    /**
     * @var UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var HelperEmail
     */
    private $helperEmail;

    /**
     * @param Context             $context
     * @param Logger              $logger,
     * @param PaymentFactory      $paymentFactory,
     * @param UserBalanceFactory  $userBalance,
     * @param HelperEmail         $helperEmail
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        Logger $logger,
        PaymentFactory $paymentFactory,
        UserBalanceFactory $userBalance,
        HelperEmail $helperEmail
    ) {
        $this->logger = $logger;
        $this->paymentFactory = $paymentFactory;
        $this->userBalance = $userBalance;
        $this->helperEmail = $helperEmail;
        parent::__construct($context);
    }

    /**
     * Affiliate User PaypalSuccess
     *
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function execute()
    {
        $data = $this->getRequest()->getParams();
        try {
            if (!empty($data) && isset($data['st']) && !empty($data['st'])
                && strtolower($data['st']) == 'completed'
            ) {
                $transdata = [
                    'aff_customer_id' => $data['id'] ,
                    'transaction_id' => $data['tx'],
                 //   'transaction_email' => $data['payer_email'],
                   // 'ipn_transaction_id' => $data['ipn_track_id'],
                  //  'transaction_date' => $data['payment_date'],
                    'transaction_status' => $data['st'],
                    'transaction_amount' => $data['amt'],
                    'transaction_currency' => $data['cc'],
                    'transaction_data' => json_encode($data),
                    'payment_method' => 'paypal_standard'
                ];

                $tranCollection = $this->paymentFactory->create()->getCollection()
                                            ->addFieldToFilter('transaction_id', ['eq' => $data['tx']])
                                            ->setPageSize(1)->setCurPage(1)->getFirstItem();
                if (!$tranCollection->getEntityId()) {
                    $paymentTmp = $this->paymentFactory->create();
                    $paymentTmp->setData($transdata);
                    $paymentTmp->save();

                    //Update balance record
                    $balanceRecord = $this->userBalance->create()->getCollection()
                                                ->addFieldToFilter('aff_customer_id', $transdata['aff_customer_id']);
                    foreach ($balanceRecord as $balRecord) {
                        $balanceAmount = (float)$balRecord->getBalanceAmount();
                        $balAmt = $balanceAmount - (float)$data['amt'];
                        $balRecord->setBalanceAmount($balAmt);
                        $balRecord->setPayNotify(0);
                        $this->_saveObj($balRecord);
                    }
                     /** send payment credited in bank mail notification to Affiliate User*/
                    $data = [
                        'email_content' => __('Your affiliate user account balance credited in your paypal account'),
                        'email_subject' => __('Payment credited notification')
                    ];
                    $this->logger->info('vcvcxxvcvcv');
                    $this->helperEmail->mailToAffPaymentCreditedNotify($transdata['aff_customer_id'], $data);
                    $this->messageManager->addSuccess(__('Your payment has been done successfully.'));
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('ipnNotify : '.$e->getMessage());
        }
       
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('affiliate/user/index/');
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_user');
    }
    
    /**
     * save Object
     *
     * @return object
     */
    private function _saveObj($object)
    {
        $object->save();
    }
}
