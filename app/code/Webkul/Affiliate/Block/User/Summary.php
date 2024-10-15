<?php
/**
 * Webkul Affiliate Summary.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Webkul\Affiliate\Model\SaleFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;
use Webkul\Affiliate\Model\UserFactory;

class Summary extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var \Webkul\Affiliate\Model\SaleFactory
     */
    private $salesFactory;

    /**
     * @var \Webkul\Affiliate\Model\UserBalanceFactory
     */
    private $userBalance;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @param Context                $context
     * @param Session                $customerSession,
     * @param PriceCurrencyInterface $priceCurrency
     * @param AffDataHelper          $affDataHelper,
     * @param SaleFactory            $salesFactory
     * @param UserBalanceFactory     $userBalance,
     * @param array                  $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PriceCurrencyInterface $priceCurrency,
        AffDataHelper $affDataHelper,
        SaleFactory $salesFactory,
        UserBalanceFactory $userBalance,
        UserFactory $userFactory,
        array $data = []
    ) {
        $this->salesFactory = $salesFactory;
        $this->userBalance = $userBalance;
        $this->priceCurrency = $priceCurrency;
        $this->userFactory = $userFactory;
        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * getSummeryDetail
     *
     */
    public function getSummeryDetail()
    {
        $summary = [];
        $affiliateId = $this->getCustomerSession()->getCustomerId();

        // get sales detail
        $salesPendingColl = $this->salesFactory->create()->getCollection()
                                            ->addFieldToFilter('aff_customer_id', $affiliateId)
                                            ->addFieldToFilter('affiliate_status', ['eq' => 0]);

        $salesApproveColl = $this->salesFactory->create()->getCollection()
                                            ->addFieldToFilter('aff_customer_id', $affiliateId)
                                            ->addFieldToFilter('affiliate_status', ['eq' => 1]);

        $summary['approved_sales'] = $salesApproveColl->getSize();
        $summary['pending_sales'] = $salesPendingColl->getSize();

        // get Balance detail
        $userBalColl = $this->userBalance->create()->getCollection()
                                            ->addFieldToFilter('aff_customer_id', $affiliateId);
        $summary['balance'] = 0;
        foreach ($userBalColl as $userBalance) {
            $summary['balance'] = $this->getFormatedPrice($userBalance->getBalanceAmount());
            $summary['clicks'] = $userBalance->getClicks();
            $summary['unique_clicks'] = $userBalance->getUniqueClicks();
        }
        return $summary;
    }

    /**
     * Get formatted by price and currency
     * @param   $price
     * @return  string
     */
    public function getFormatedPrice($price)
    {
        return $this->priceCurrency->format($price, true, 2, null, $this->getCurrentCurrencyCode());
    }

    /**
     * Get formatted by price and currency
     * @param   $price
     * @return  string
     */
    public function getAffCommissionDetail()
    {
        $affiliateId = $this->getCustomerSession()->getCustomerId();
        $affUserColl = $this->userFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('customer_id', $affiliateId);
        $affUserData = false;
        foreach ($affUserColl as $affUser) {
            $affUserData = [
                'pay_per_click' => $this->getFormatedPrice($affUser->getPayPerClick()),
                'pay_per_unique_click' => $this->getFormatedPrice($affUser->getPayPerUniqueClick()),
                'per_sale' => $affUser->getCommissionType() == 'fixed' ?
                                $this->getFormatedPrice($affUser->getCommission()):
                                $affUser->getCommission()." %"
            ];
        }
        return $affUserData;
    }

     /**
      * Get getTrafficRecordUrl
      * @return string
      */
    public function getTrafficRecordUrl($type)
    {
        return $this->getUrl('affiliate/user/traffic', ['u' => $type,'_secure' => $this->getRequest()->isSecure()]);
    }
}
