<?php
/**
 * Webkul Affiliate User Status.
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
use Webkul\Affiliate\Helper\Data as AffDataHelper;

class UserAbstract extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Webkul\Affiliate\Helper\Data
     */
    public $affDataHelper;

    /**
     * @param Context         $context
     * @param Session         $customerSession,
     * @param RedirectFactory $redirect,
     * @param AffDataHelper   $affDataHelper,
     * @param array           $data
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        array $data = []
    ) {

        $this->customerSession = $customerSession;
        $this->affDataHelper = $affDataHelper;
        parent::__construct($context, $data);
    }

    /**
     * isAffiliate for check affiliate user status
     * @return array;
     */
    public function isAffiliate()
    {
        return $this->affDataHelper->isAffiliateUser($this->customerSession->getCustomerId());
    }

    /**
     * getAffiliateConfig
     * @return array
     */
    public function getAffiliateConfig()
    {
        return $this->affDataHelper->getAffiliateConfig();
    }

    /**
     * getCustomerSession
     * @return Magento\Customer\Model\Session
     */
    public function getCustomerSession()
    {
        return $this->customerSession;
    }

    /**
     * getCurrentCurrencyCode
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->affDataHelper->getCurrentCurrencyCode();
    }
}
