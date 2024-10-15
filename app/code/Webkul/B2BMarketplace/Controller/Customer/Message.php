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

namespace Webkul\B2BMarketplace\Controller\Customer;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Message extends Action
{
    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\B2BMarketplace\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_customerSession = $customerSession;
        $this->_helper = $helper;
        $this->_url = $url;
        $this->_session = $session;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(__("Supplier's Message"));

        return $resultPage;
    }
}
