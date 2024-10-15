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
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

class Login extends Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\B2BMarketplace\Helper\Data $helper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        if ($this->_helper->isUserLoggedIn()) {
            return $this->resultRedirectFactory
                        ->create()
                        ->setPath('*/*/account');
        } else {
            $resultPage = $this->_resultPageFactory->create();
            $resultPage->getConfig()->getTitle()->set(__('Supplier Log In'));
            return $resultPage;
        }
    }
}
