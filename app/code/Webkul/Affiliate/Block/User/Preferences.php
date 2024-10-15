<?php
/**
 * Webkul Affiliate Preferences.
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
use Webkul\Affiliate\Model\Config\Source\AffiliateAllowedPaymentMethodsList as AffAllowPayMethods;

class Preferences extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * @var \Webkul\Affiliate\Model\Config\Source\AffiliateAllowedPaymentMethodsList
     */
    private $affAllowPayMethods;

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
        AffAllowPayMethods $affAllowPayMethods,
        array $data = []
    ) {

        $this->affAllowPayMethods = $affAllowPayMethods;

        parent::__construct($context, $customerSession, $affDataHelper, $data);
    }

    /**
     * getSaveAction
     * @return array
     */
    public function getAllowedPaymentMethod()
    {
        $config = $this->getAffiliateConfig();
        return explode(',', $config['payment_methods']);
    }

    /**
     * getPayMethodLabel
     * @param string $methodCode
     * @param string
     */
    public function getPayMethodLabel($methodCode)
    {
        return $this->affAllowPayMethods->getOptionText($methodCode);
    }

    /**
     * getSaveAction
     */
    public function getSaveAction()
    {
        return $this->getUrl('affiliate/user/preferences', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getPaymentMethodData\
     * @return json
     */
    public function getPaymentMethodData()
    {
        if ($affiliate = $this->isAffiliate()) {
            $paymtData = $affiliate['data']->getCurrentPaymentMethod();
            if ($paymtData == '') {
                $paymtData = '{"payment_method":"checkmo","account_data":{}}';
            }
            return $paymtData;
        }
    }
}
