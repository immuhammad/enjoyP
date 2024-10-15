<?php
/**
 * Webkul Affiliate RegisterLink.
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

/**
 * "Orders and Returns" link
 */
class RegisterLink extends \Magento\Framework\View\Element\Html\Link\Current
{
    public function __construct(
        Context $context,
        Session $customerSession,
        AffDataHelper $affDataHelper,
        \Magento\Framework\App\DefaultPathInterface $defaultPath,
        array $data = []
    ) {

        $this->customerSession = $customerSession;
        $this->affDataHelper = $affDataHelper;
        parent::__construct($context, $defaultPath, $data);
    }

    /**
     * @return string
     */
    public function _toHtml()
    {
        $affConfig = $this->affDataHelper->getAffiliateConfig();
        $affiliateStatus1 = $affConfig['enable'];
        $affiliateStatus2 = $affConfig['registration'];
        if ($affiliateStatus1 && $affiliateStatus2 && !$this->customerSession->isLoggedIn()) {
            return parent::_toHtml();
        }
        return '';
    }
}
