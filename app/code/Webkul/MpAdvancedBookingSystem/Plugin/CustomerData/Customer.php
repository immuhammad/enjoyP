<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Plugin\CustomerData;

class Customer
{
    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Customer\Helper\View
     */
    private $customerViewHelper;

    /**
     * Constructor
     *
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Customer\Helper\View                    $customerViewHelper
     */
    public function __construct(
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Customer\Helper\View $customerViewHelper
    ) {
        $this->currentCustomer = $currentCustomer;
        $this->customerViewHelper = $customerViewHelper;
    }

    /**
     * AfterGetSectionData
     *
     * @param \Magento\Customer\CustomerData\Customer $subject
     * @param mixed $result
     */
    public function afterGetSectionData(\Magento\Customer\CustomerData\Customer $subject, $result)
    {
        if ($this->currentCustomer->getCustomerId()) {
            $customer = $this->currentCustomer->getCustomer();
            $result['email'] = $customer->getEmail();
        }
        return $result;
    }
}
