<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpStripe
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpStripe\Block\Adminhtml\Manage;

use Webkul\MpStripe\Model\StripeSellerFactory;

class ConnectAccount extends \Magento\Backend\Block\Template
{
    /**
     * Block template.
     *
     * @var string
     */
    protected $_template = 'Webkul_MpStripe::connectaccount.phtml';

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param StripeSellerFactory $stripeSeller
     * @param \Webkul\MpStripe\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        StripeSellerFactory $stripeSeller,
        \Webkul\MpStripe\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->stripeSeller = $stripeSeller;
        $this->helper = $helper;
    }

    /**
     * To get stripe seller information from stripe
     *
     * @return array
     */
    public function getStripeSellerInformation()
    {
        $stripeAccount = '';
        $sellerId = $this->getRequest()->getParam('seller_id');
        $sellerData = $this->stripeSeller->create()
        ->getCollection()->addFieldToFilter('seller_id', $sellerId)->getFirstItem();
        $this->helper->setUpDefaultDetails();
        if ($sellerData->getStripeUserId()) {
            $stripeAccount = \Stripe\Account::retrieve($sellerData->getStripeUserId());
        }
        return $stripeAccount;
    }

    /**
     * GetStripeSellerFactory get seller wise data.
     *
     * @return \Webkul\MpStripe\Model\ResourceModel\StripeSeller\Collection
     */
    public function getStripeSellerFactory()
    {
        $sellerId = $this->getRequest()->getParam('seller_id');
        return $this->stripeSeller
            ->create()->getCollection()
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            )
            ->addFieldToFilter(
                'payment_environment',
                ['eq' => $this->helper->getConfigValue('debug')]
            );
    }

    /**
     * To get stripe custom account link
     *
     * @return array
     */
    public function getAccountLink()
    {
        $sellerCollection = $this->getStripeSellerFactory();
        $this->helper->setUpDefaultDetails();
        $sellerId = $this->getRequest()->getParam('seller_id');
        if ($sellerCollection->getSize() > 0) {
            $accountLinks = \Stripe\AccountLink::create([
                'account' => $sellerCollection->getFirstItem()->getStripeUserId(),
                'refresh_url' => $this->getUrl('mpstripe/manage/account', ['seller_id' => $sellerId]),
                'return_url' => $this->getUrl('mpstripe/manage/account', ['seller_id' => $sellerId]),
                'type' => 'account_onboarding',
                'collect' => 'eventually_due',
            ]);
            return $accountLinks;
        } else {
            return [];
        }
    }

    /**
     * Consent message
     *
     * @return string
     */
    public function getConsentMessage()
    {
        $serviceAgreement = __('Services Agreement');
        $connectedAccountAgreement = __("Connected Account Agreement");
        $remainingMsg = __('certify that the information you have provided is complete and correct');
        return __(
            "By creating account, you agree to our %1, %2, and %3",
            '<a href="https://stripe.com/gb/legal" target="__blank">'.$serviceAgreement.'</a>',
            '<a href="https://stripe.com/gb/connect-account/legal" target="__blank">'.$connectedAccountAgreement.'</a>',
            $remainingMsg
        );
    }
}
