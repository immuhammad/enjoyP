<?php
/**
 * Webkul Software.
 * @category Webkul
 * @package Webkul_Affiliate
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\Adminhtml\Customer\Edit;
 
use Magento\Backend\Block\Template\Context;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Webkul\Affiliate\Model\Config\Source\AffiliateAllowedPaymentMethodsList as AffAllowPayMethods;
use Webkul\Affiliate\Model\UserFactory;
 
class AffiliatePayment extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    public $_template = 'customer/affiliatepayment.phtml';

    /**
     * @var \Webkul\Affiliate\Helper\Data
     */
    private $affDataHelper;

    /**
     * @var \Webkul\Affiliate\Model\Config\Source\AffiliateAllowedPaymentMethodsList
     */
    private $affAllowPayMethods;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    private $logger;
 
    /**
     * @param Context $context
     * @param \Webkul\Affiliate\Helper\Data $affDataHelper
     * @param \Webkul\Affiliate\Model\Config\Source\AffiliateAllowedPaymentMethodsList $affAllowPayMethods
     * @param \Webkul\Affiliate\Model\UserFactory $userFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        AffDataHelper $affDataHelper,
        AffAllowPayMethods $affAllowPayMethods,
        UserFactory $userFactory,
        \Webkul\Affiliate\Logger\Logger $logger,
        array $data = []
    ) {
        $this->affDataHelper = $affDataHelper ;
        $this->affAllowPayMethods = $affAllowPayMethods;
        $this->userFactory = $userFactory;
        $this->logger = $logger;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getAllowedPaymentMethod()
    {
        $config = $this->affDataHelper->getAffiliateConfig();
        return explode(',', $config['payment_methods']);
    }

    /**
     * @return json
     */
    public function getPaymentMethodData()
    {
        $customerId = $this->getRequest()->getParam('id');
        
        $paymtData = '{"payment_method":"checkmo","account_data":{}}';
        if ($customerId) {
            $affiliateUser = $this->userFactory->create()->getCollection()
                                        ->addFieldTOFilter('customer_id', ['eq' => $customerId])
                                        ->setPageSize(1)->setCurPage(1)->getFirstItem();
            
            if ($affiliateUser->getId()) {
                $paymtDataTmp = $affiliateUser->getCurrentPaymentMethod();
                
                if ($paymtDataTmp != '') {
                    $paymtData = $paymtDataTmp;
                }
            }
        }
        return $paymtData;
    }

    /**
     * @param string $methodCode
     * @return string
     */
    public function getPayMethodLabel($methodCode)
    {
        return $this->affAllowPayMethods->getOptionText($methodCode);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Affiliate Payment Preference');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Affiliate Payment Preference');
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return false;
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }
}
