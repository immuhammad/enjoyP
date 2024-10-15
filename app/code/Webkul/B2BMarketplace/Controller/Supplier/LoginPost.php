<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Supplier;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul Marketplace Account BecomesellerPost Controller.
 */
class LoginPost extends Action
{
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Helper\Data $helper
    ) {
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->getRequest()->isPost()) {
            if ($this->_helper->isUserLoggedIn()) {
                return $this->resultRedirectFactory
                            ->create()
                            ->setPath('*/*/account');
            } else {
                $email = trim($this->getRequest()->getParam("supplier_email"));
                $password = trim($this->getRequest()->getParam("supplier_password"));

                if ($this->_helper->authenticate($email, $password)) {
                    return $this->resultRedirectFactory
                                ->create()
                                ->setPath('*/*/account');
                } else {
                    return $this->resultRedirectFactory
                                ->create()
                                ->setPath('*/*/account');
                }
            }
        } else {
            return $this->resultRedirectFactory
                                ->create()
                                ->setPath('*/*/account');
        }
    }
}
