<?php
/**
 * Webkul Affiliate Each Controller Access.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Webkul\Affiliate\Model\ClicksFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Model\UserFactory;
use Magento\Checkout\Model\Session as CheckoutSession;

/**
 * Webkul Affiliate ControllerActionPredispatch Observer.
 */
class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $timezone;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\Affiliate\Model\ClicksFactory
     */
    private $clicksFactory;

    /**
     * @var \Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @param StoreManagerInterface $storeManagerInterface,
     * @param TimezoneInterface $timezone,
     * @param Session $customerSession,
     * @param ClicksFactory $clicksFactory,
     * @param UserBalanceFactory $userBalance,
     * @param UserFactory $userFactory
     */

    public function __construct(
        StoreManagerInterface $storeManagerInterface,
        TimezoneInterface $timezone,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        ClicksFactory $clicksFactory,
        UserBalanceFactory $userBalance,
        \Webkul\Affiliate\Logger\Logger $logger,
        UserFactory $userFactory
    ) {
        $this->storeManager = $storeManagerInterface;
        $this->timezone = $timezone;
        $this->checkoutSession = $checkoutSession;
        $this->customerSession = $customerSession;
        $this->clicksFactory = $clicksFactory;
        $this->userBalance = $userBalance;
        $this->logger = $logger;
        $this->userFactory = $userFactory;
    }

    /**
     * customer register event handler.
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $request = $observer->getControllerAction()->getRequest();
        $postData = $request->getParams();
        if (empty($request->getRouteName())) {
            return false;
        }
        $this->logger->info('controller_action_predispatch_' . $request->getRouteName());
        $serverData = $request->getServer();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        if (($serverData->get('HTTP_REFERER')) && strpos($serverData->get('HTTP_REFERER'), $baseUrl)===false
        && isset($postData['aff_id'])
        && $postData['aff_id']) {
            $hitData = explode('catalog/product/view/id/', $request->getPathInfo());
            $hitId = isset($hitData[1])? $hitData[1] : $request->getParam('banner');
            $hitType  = isset($hitData[1])? 'product' : 'textbanner';
            $data = [
                'customer_ip'       =>  $serverData->get('REMOTE_ADDR'),
                'customer_domain'   =>  $serverData->get('HTTP_HOST'),
                'hit_id'            =>  $hitId,
                'hit_type'          =>  $hitType,
                'aff_customer_id'   =>  $postData['aff_id'],
                'commission'    =>  '',
                'come_from'         =>  $serverData->get('HTTP_REFERER'),
            ];
            $clickDetail = $this->getAffUserClickAndComm($postData['aff_id'], $data);
            $data['commission'] = $clickDetail['comm'];

            /** save click detail*/
            $clickTmpColl = $this->clicksFactory->create()->getCollection()
            ->addFieldToFilter('customer_ip', $data['customer_ip'])
            ->addFieldToFilter('customer_domain', $data['customer_domain'])
            ->addFieldToFilter('hit_id', $data['hit_id'])
            ->addFieldToFilter('hit_type', $data['hit_type'])
            ->addFieldToFilter('aff_customer_id', $data['aff_customer_id'])
            ->addFieldToFilter('commission', $data['commission'])
            ->addFieldToFilter('come_from', $data['come_from']);
            $clickTmpColl->getSelect()->where('created_at = CURRENT_TIMESTAMP');
            if ($clickTmpColl->getSize()) {
                return false;
            }
            $clickTmp = $this->clicksFactory->create();
            $clickTmp->setData($data);
            $clickTmp->save();

            // update balance data

            if ($clickDetail) {
                $userBalanceColl = $this->userBalance->create()->getCollection()
                                        ->addFieldToFilter(
                                            'aff_customer_id',
                                            ['eq' => $postData['aff_id']]
                                        );

                if ($userBalanceColl->getSize()) {
                    foreach ($userBalanceColl as $userBalance) {
                        $clicks = $clickDetail['click'] + (int) $userBalance->getClicks();
                        $uniqueClicks = $clickDetail['unique_click']
                                        + (int) $userBalance->getUniqueClicks();
                        $balanceAmount = $userBalance->getBalanceAmount()
                                        +$clickDetail['comm'];

                        $userBalance->setClicks($clicks);
                        $userBalance->setUniqueClicks($uniqueClicks);
                        $userBalance->setBalanceAmount($balanceAmount);
                        $this->_saveObject($userBalance);
                    }
                } else {
                    $dataTmp = [
                        'aff_customer_id' => $postData['aff_id'],
                        'clicks' => $clickDetail['click'],
                        'unique_clicks' => $clickDetail['unique_click'],
                        'balance_amount' => $clickDetail['comm']
                    ];
                    $tempBal = $this->userBalance->create();
                    $tempBal->setData($dataTmp);
                    $tempBal->save();
                }
                // save in session
                $totalAffIds = $this->customerSession->getData('aff_ids');
                if (empty($totalAffIds) || $totalAffIds=="") {
                    $totalAffIds = $this->checkoutSession->getData('aff_ids');
                }

                if (!empty($totalAffIds)) {
                    $status = true;
                    foreach ($totalAffIds as $affData) {
                        if ($affData['hit_id'] == $data['hit_id']) {
                            $status = false;
                        }
                    }
                    if ($status) {
                        array_push($totalAffIds, $data);
                        $this->customerSession->setData('aff_ids', $totalAffIds);
                        $this->checkoutSession->setData('aff_ids', $totalAffIds);
                    }
                } else {
                    $totalAffIds = [$data];
                    $this->customerSession->setData('aff_ids', $totalAffIds);
                    $this->checkoutSession->setData('aff_ids', $totalAffIds);
                }
            }
        }
    }

    /**
     * Check if unique click
     * @param array
     * @return bool
     */

    private function getIsUniqueClick($data)
    {
        $clickColl = $this->clicksFactory->create()->getCollection()
                                            ->addFieldToFilter('customer_ip', ['eq' => $data['customer_ip']])
                                            ->addFieldToFilter('aff_customer_id', ['eq' => $data['aff_customer_id']])
                                            ->setPageSize(1)->setCurPage(1)->getFirstItem();
        return $clickColl->getEntityId() ? false : true;
    }

    /**
     * Get Affiliate user click and commission detail
     * @param int affId
     * @param array $data
     * @return false|array
     */

    private function getAffUserClickAndComm($affId, $data)
    {
        $affUserColl = $this->userFactory->create()->getCollection()->addFieldToFilter('customer_id', $affId)
                                                    ->setPageSize(1)->setCurPage(1)->getFirstItem();
        $response = false;
        if ($affUserColl->getEntityId()) {
            $response = [];
            if ($this->getIsUniqueClick($data)) {
                $response['comm'] = $affUserColl->getPayPerUniqueClick();
                $response['unique_click'] = 1;
                $response['click'] = 1;
            } else {
                $response['unique_click'] = 0;
                $response['click'] = 1;
                $response['comm'] = $affUserColl->getPayPerClick();
            }
        }
        return $response;
    }

    /**
     * save object
     * @param Object $object
     */
    private function _saveObject($object)
    {
        $object->save();
    }
}
