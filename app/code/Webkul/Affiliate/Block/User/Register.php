<?php
/**
 * Webkul Affiliate User Create.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Webkul\Affiliate\Helper\Data as AffDataHelper;
use Magento\Cms\Model\Template\FilterProvider;

class Register extends \Magento\Framework\View\Element\Template
{
    /**
     * isAffilateRegistration
     * @return bool
     */
    private $affDataHelper;
    private $customerSession;

    public function __construct(
        Context $context,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        FilterProvider $filterProvider,
        array $data = []
    ) {
        $this->filterProvider = $filterProvider;
        $this->customerSession = $customerSession;
        $this->affDataHelper = $affDataHelper;
        parent::__construct($context, $data);
    }
    public function isAffilateEnable()
    {
        return $this->_scopeConfig->getValue(
            'affiliate/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
    public function isAffilateRegistration()
    {
        $affiliateStatus = $this->_scopeConfig->getValue(
            'affiliate/general/registration',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $affValue = $this->getRequest()->getParam('aff');
        return ($affValue==1 && $affiliateStatus) ? true : false;
    }
    public function _prepareLayout()
    {
   //set page title
        if ($this->getRequest()->getParam('aff')
        && $this->affDataHelper->getConfigData()
        && $this->_scopeConfig->getValue(
            'affiliate/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        )
        ) {
            $this->pageConfig->getTitle()->set(__('Create Affiliate Customer Account'));
        }
        return parent::_prepareLayout();
    }
    /**
     * isAffilateRegistration
     * @return bool
     */
    public function isAffilateRegistrationTerms()
    {
        return $this->affDataHelper->filterContent(
            $this->_scopeConfig->getValue(
                'affiliate/terms/editor_textarea',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
        );
    }

    public function getErrorUrl()
    {
        return $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
    }
    public function getConfigBlogLinkValue()
    {
        return $this->affDataHelper->getConfigDataBlogLink();
    }
    public function getBlogUrlHintonregistration()
    {
        return $this->_scopeConfig->getValue(
            'affiliate/general/blog_url_hint',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
