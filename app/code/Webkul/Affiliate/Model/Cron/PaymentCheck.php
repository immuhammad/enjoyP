<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Model\Cron;

use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Helper\Data as AffiliateHelper;
use Magento\Framework\Stdlib\DateTime\DateTime;

class PaymentCheck
{
    /**
     * @var UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var AffiliateHelper
     */
    private $affDataHelper;

    /**
     * @param DateTime             $dateTime
     * @param UserBalanceFactory   $userBalance
     * @param AffiliateHelper      $affiliateHelper
     */
    public function __construct(
        DateTime $dateTime,
        UserBalanceFactory $userBalance,
        AffiliateHelper $affiliateHelper
    ) {
        $this->dateTime    = $dateTime;
        $this->userBalance = $userBalance;
        $this->affDataHelper = $affiliateHelper;
    }

    /**
     * Change pay notify status
     *
     * @return void
     */
    public function execute()
    {
        $affiConfig = $this->affDataHelper->getAffiliateConfig();
        $todayDate = $this->dateTime->gmtDate('d');
        if ($todayDate == $affiConfig['pay_date']) {
            $userBalanceCloll = $this->userBalance->create()->getCollection()->addFieldToFilter('pay_notify', 0)
                                        ->addFieldToFilter('balance_amount', ['gteq' => $affiConfig['min_pay_bal']]);
            foreach ($userBalanceCloll as $userBalance) {
                $userBalance->setPayNotify(1);
                $this->_saveObject($userBalance);
            }
        }
    }

    /**
     * _saveObject
     * @param Object $object
     * @return void
     */
    private function _saveObject($object)
    {
        $object->save();
    }
}
