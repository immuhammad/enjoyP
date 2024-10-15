<?php

/**
 * Webkul_Affiliate data helper
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Helper;

use Webkul\Affiliate\Model\UserFactory;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context  $context
     * @param UserFactory $userFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        StoreManagerInterface $storeManager,
        UserFactory $userFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->urlInterface = $urlInterface;
        $this->userFactory = $userFactory;
        $this->storeManager  = $storeManager;
        $this->filterProvider = $filterProvider;
        parent::__construct($context);
    }

     /**
      * Get Configuration Detail of Affiliate
      * @return array of Affiliate Configuration Detail
      */

    public function getAffiliateConfig()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $affiliateConfig = [
            'enable' => $this->scopeConfig->getValue('affiliate/general/enable', $storeScope),
            // 'referUrlCompare' => $this->scopeConfig->getValue('affiliate/general/referUrlCompare', $storeScope),
            'priority' => $this->scopeConfig->getValue('affiliate/general/priority', $storeScope),
            'registration' => $this->scopeConfig->getValue('affiliate/general/registration', $storeScope),
            'auto_approve' => $this->scopeConfig->getValue('affiliate/general/auto_approve', $storeScope),
            'min_pay_bal' => $this->scopeConfig->getValue('affiliate/general/min_pay_bal', $storeScope),
            'pay_date' => $this->scopeConfig->getValue('affiliate/general/pay_date', $storeScope),
            'blog_url_hint' => $this->scopeConfig->getValue('affiliate/general/blog_url_hint', $storeScope),
            'aff_email_campaign' => $this->scopeConfig->getValue(
                'affiliate/general/email_campaign_template',
                $storeScope
            ),
            'manager_email' => $this->scopeConfig
                             ->getValue('affiliate/general/manager_email', $storeScope),
            'manager_email_template' => $this->scopeConfig->getValue(
                'affiliate/general/manager_email_template',
                $storeScope
            ),
            'payment_credit_template' => $this->scopeConfig
                                                ->getValue(
                                                    'affiliate/general/payment_credit_notify_email_template',
                                                    $storeScope
                                                ),
            'user_notify_by_admin' => $this->scopeConfig
                                            ->getValue(
                                                'affiliate/general/aff_user_notify_by_admin_email_template',
                                                $storeScope
                                            ),
            'manager_first_name' => $this->scopeConfig->getValue('affiliate/payment/manager_first_name', $storeScope),
            'manager_last_name' => $this->scopeConfig->getValue('affiliate/payment/manager_last_name', $storeScope),
            'sandbox' => $this->scopeConfig->getValue('affiliate/payment/sandbox', $storeScope),
            'payment_methods' => $this->scopeConfig->getValue('affiliate/payment/payment_methods', $storeScope),
            'per_click' => $this->scopeConfig->getValue('affiliate/commission/per_click', $storeScope),
            'unique_click' => $this->scopeConfig->getValue('affiliate/commission/unique_click', $storeScope),
            'type_on_sale' => $this->scopeConfig->getValue('affiliate/commission/type_on_sale', $storeScope),
            'rate' => $this->scopeConfig->getValue('affiliate/commission/rate', $storeScope),
            'editor_textarea' => $this->scopeConfig->getValue('affiliate/terms/editor_textarea', $storeScope)
        ];
        return $affiliateConfig;
    }

    /**
     * isAffiliateUser check user is affiliate or not
     * @param int $affiliateUserId
     * @return false|array $responce
     */

    public function isAffiliateUser($affiliateUserId)
    {
        $affiliateColl = $this->userFactory->create()->getCollection()
                                            ->addFieldToFilter('customer_id', $affiliateUserId);
        $responce = false;
        if ($affiliateColl->getSize()) {
            foreach ($affiliateColl as $affiliateUser) {
                $responce = [
                    'user' => true,
                    'status' => $affiliateUser->getEnable(),
                    'data' => $affiliateUser
                ];
            }
        }
        return $responce;
    }

    /**
     * getCurrentCurrencyCode
     * @return string current currency code
     */
    public function getCurrentCurrencyCode()
    {
        return $this->storeManager->getStore()->getCurrentCurrencyCode();
    }
    public function getConfigData()
    {
        return $this->scopeConfig->getValue(
            'affiliate/general/registration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getConfigDataBlogLink()
    {
        return $this->scopeConfig->getValue(
            'affiliate/general/blog',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function getRegistrationUrl()
    {
        $url = $this->urlInterface->getUrl('customer/account/login');
        return $url;
    }

    //filter content to get the media files
    public function filterContent($content)
    {
        if (!empty($content)) {
            return $this->filterProvider->getPageFilter()->filter($content);
        }
    }
}
