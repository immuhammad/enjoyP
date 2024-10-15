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

namespace Webkul\B2BMarketplace\Controller\Supplier\Verification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

class Send extends Action
{
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\B2BMarketplace\Helper\Data $helper,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;

        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
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
        $helper = $this->_objectManager->create(\Webkul\Marketplace\Helper\Data::class);
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $this->_helper->processVerifySupplier();
            return $this->resultRedirectFactory->create()->setPath('b2bmarketplace/supplier/verification');
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
