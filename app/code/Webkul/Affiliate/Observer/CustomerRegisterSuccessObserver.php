<?php
/**
 * Webkul Affiliate User Register.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Affiliate\Model\UserFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Framework\Notification\NotifierInterface;

/**
 * Webkul Affiliate CustomerRegisterSuccessObserver Observer.
 */
class CustomerRegisterSuccessObserver implements ObserverInterface
{
    /**
     * @var UserFactory
     */
    private $userFactory;
    private $scopeConfig;
    private $customerSession;
    /**
     * @var userBalance
     */
    private $userBalance;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * @var AffDataHelper
     */
    private $affDataHelper;

    /**
     * @var \Magento\Framework\Notification\NotifierInterface
     */
    protected $notifierPool;

    /**
     * @param UserFactory         $userFactory
     * @param UserBalanceFactory  $userBalance
     * @param ManagerInterface    $managerInterface
     * @param AffDataHelper       $affDataHelper
     * @param NotifierInterface   $notifierPool
     */
    public function __construct(
        UserFactory $userFactory,
        UserBalanceFactory $userBalance,
        ManagerInterface $managerInterface,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        AffDataHelper $affDataHelper,
        NotifierInterface $notifierPool
    ) {
        $this->customerSession = $customerSession;
        $this->userFactory = $userFactory;
        $this->userBalance = $userBalance;
        $this->scopeConfig = $scopeConfig;
        $this->messageManager = $managerInterface;
        $this->affDataHelper = $affDataHelper;
        $this->notifierPool = $notifierPool;
    }

    /**
     * customer register event handler.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        
        $affRegistration=$this->scopeConfig->getValue('affiliate/general/registration');
        if ($affRegistration==1) {
            $data = $observer['account_controller'];
            try {
                $paramData = $data->getRequest()->getParams();
                if (!empty($paramData['aff_conf']) && $paramData['aff_conf'] == 1) {
                    $customerId = $observer->getCustomer()->getId();
                    $affiliateColl = $this->userFactory->create()->getCollection()
                                                        ->addFieldToFilter('customer_id', $customerId);
                    if ($affiliateColl->getSize() == 0) {
                        $affiConfig = $this->affDataHelper->getAffiliateConfig();
                        $affData = [
                            'customer_id' => $customerId,
                            'pay_per_click' => $affiConfig['per_click'],
                            'pay_per_unique_click' => $affiConfig['unique_click'],
                            'commission_type' => $affiConfig['type_on_sale'],
                            'commission' => $affiConfig['rate'],
                        ];
                        if (isset($paramData['bloglink'])) {
                            $postValues = $paramData['bloglink'];
                            $affData['blog_url'] = $postValues;
                        }
                        $name = $observer->getCustomer()->getFirstName().' '.$observer->getCustomer()->getLastName();
                        if ($affiConfig['auto_approve']) {
                            $affData['enable'] = 1;
                            $msg = 'New customer '.$name.' with email '.$observer->getCustomer()->getEmail().' registered';
                        } else {
                            $affData['enable'] = 0;
                            $msg = 'New customer '.$name.' with email '.$observer->getCustomer()->getEmail().' ';
                            $msg .= 'registered and customer waiting for approval';
                        }
                        $tempAff = $this->userFactory->create();
                        $tempAff->setData($affData);
                        $tempAff->save();

                        $tempUserBal = $this->userBalance->create();
                        $tempUserBal->setData(['aff_customer_id' => $customerId]);
                        $tempUserBal->save();

                        $this->notifierPool->addNotice(
                            'Affiliate Registration',
                            $msg
                        );
                        $this->messageManager->addSuccess(__('Affiliate account created successfully.'));
                    }
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Affiliate Registrations are not allowed.'));
        }
    }
}
