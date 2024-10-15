<?php

/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Data as AffiliateHelper;

class PaymentNotifyUpdate extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var AffiliateHelper
     */
    private $affDataHelper;

    /**
     * @param Context             $context,
     * @param JsonFactory         $resultJsonFactory,
     * @param UserBalanceFactory  $userBalance,
     * @param AffiliateHelper     $affiliateHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        UserBalanceFactory $userBalance,
        AffiliateHelper $affiliateHelper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->userBalance = $userBalance;
        $this->affDataHelper = $affiliateHelper;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::config_affiliate');
    }

    /**
     * Affiliate user payment notify
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        try {
            $affiConfig = $this->affDataHelper->getAffiliateConfig();
            $userBalanceCloll = $this->userBalance->create()->getCollection()->addFieldToFilter('pay_notify', 0)
                                        ->addFieldToFilter('balance_amount', ['gteq' => $affiConfig['min_pay_bal']]);
            foreach ($userBalanceCloll as $userBalance) {
                $userBalance->setPayNotify(1);
                $this->_saveObject($userBalance);
            }
            $result = ['success' => true, 'msg' => __('Affiliate user payment notify updated successfully.')];
            return $resultJson->setData($result);
        } catch (\Exception $e) {
            $result = ['success' => false, 'msg' => $e->getMessage()];
            return $resultJson->setData($result);
        }
    }

    /**
     * _saveObject
     * @param Object $object
     */
    private function _saveObject($object)
    {
        $object->save();
    }
}
