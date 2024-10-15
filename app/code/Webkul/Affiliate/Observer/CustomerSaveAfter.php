<?php
/**
 * Webkul Software.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Model\UserFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Logger\Logger;

/**
 * Webkul Affiliate CustomerSaveAfter Observer Model.
 */
class CustomerSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Webkul\Affiliate\Logger\Logger
     */
    private $logger;

    /**
     * @param Session            $customerSession,
     * @param UserFactory        $userFactory,
     * @param UserBalanceFactory $userBalance,
     * @param Logger             $logger
     */
    public function __construct(
        Session $customerSession,
        UserFactory $userFactory,
        UserBalanceFactory $userBalance,
        Logger $logger
    ) {
    
        $this->customerSession = $customerSession;
        $this->userFactory = $userFactory;
        $this->userBalance = $userBalance;
        $this->logger = $logger;
    }

    /**
     * Customer save after event handler.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $customerId = $observer->getCustomer()->getId();
            $affiliateData = $observer->getRequest()->getParams();
            if (isset($affiliateData['affiliate']) || isset($affiliateData['aff_payment_method'])) {
                $userData = $this->userFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('customer_id', $customerId)
                                            ->setPageSize(1)
                                            ->setCurPage(1)
                                            ->getFirstItem();
                if (isset($affiliateData['account_data'])) {
                    $paymentData = [
                        'payment_method' => $affiliateData['aff_payment_method'],
                        'account_data' => $affiliateData['account_data']
                    ];
                    $affiliateData['affiliate']['current_payment_method'] = json_encode($paymentData);
                }
                if ($userData->getEntityId()) {
                    foreach ($affiliateData['affiliate'] as $key => $value) {
                        $userData->setData($key, $value);
                    }
                    $userData->save();
                } else {
                    $affTmpSale = $this->userFactory->create();
                    $affiliateData['affiliate']['customer_id'] = $customerId;
                    $affTmpSale->setData($affiliateData['affiliate']);
                    $affTmpSale->save();

                    $tempUserBal = $this->userBalance->create();
                    $tempUserBal->setData(['aff_customer_id' => $customerId]);
                    $tempUserBal->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('affiliate customer save : '.$e->getMessage());
        }
    }
}
