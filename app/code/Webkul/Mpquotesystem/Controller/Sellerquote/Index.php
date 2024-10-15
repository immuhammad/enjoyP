<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Controller\Sellerquote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context                         $context
     * @param PageFactory                     $_resultPageFactory
     * @param \Magento\Customer\Model\Url     $urlModel
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        Context $context,
        PageFactory $_resultPageFactory,
        \Magento\Customer\Model\Url $urlModel,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_resultPageFactory = $_resultPageFactory;
        $this->_urlModel = $urlModel;
        $this->_mpHelper = $mpHelper;
        $this->_customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * Check customer authentication
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_urlModel->getLoginUrl();

        if (!$this->_customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * MpAmazonConnector Detail page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /**
         * @var \Magento\Backend\Model\View\Result\Page $resultPage
         */
        $helper = $this->_mpHelper;
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $resultPage = $this->_resultPageFactory->create();
            if ($helper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpquotesystem_sellerquote_index_layout2');
            }
            $resultPage->getConfig()->getTitle()->set(
                __('Quote Settings')
            );

            return $resultPage;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
