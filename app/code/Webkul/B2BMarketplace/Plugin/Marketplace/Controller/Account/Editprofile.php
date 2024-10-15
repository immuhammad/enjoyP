<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Plugin\Marketplace\Controller\Account;

class Editprofile
{
    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    private $_helper;

    public function __construct(
        \Webkul\B2BMarketplace\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterExecute(
        \Webkul\Marketplace\Controller\Account\Editprofile $subject,
        $result
    ) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $resultRedirect = $objectManager->get(\Magento\Framework\Controller\Result\Redirect::class);

        return $resultRedirect->setPath('b2bmarketplace/supplier/settings');
    }
}
