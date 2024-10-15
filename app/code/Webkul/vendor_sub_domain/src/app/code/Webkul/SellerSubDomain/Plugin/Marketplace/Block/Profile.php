<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Plugin\Marketplace\Block;

use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session as CustomerSession;

class Profile
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @param Context                             $context
     * @param CustomerSession                     $customerSession
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_objectManager = $context->getObjectManager();
        $this->_customerSession = $customerSession;
        $this->_helper = $data;
    }

    /**
     * @param \Webkul\Marketplace\Block\Collection $subject
     * @param callable $proceed
     * @param $value = ''
     * @return \Webkul\Marketplace\Model\SellerFactory
     */
    public function aroundGetProfileDetail(\Webkul\Marketplace\Block\Profile $subject, callable $proceed, $value = '')
    {
        if ($this->_helper->isModuleEnable() && $this->_helper->getShopNameByCurrentUrl()) {
            return $this->_helper->getProfileDetail($value);
        }
        return $proceed($value);
    }
}
