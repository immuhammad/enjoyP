<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

/**
 *  Webkul Mpmembership Index controller
 */
class Index extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $customerModel;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerModel
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        \Webkul\Mpmembership\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerModel,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        parent::__construct($context);
        $this->session = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helper     = $helper;
        $this->customerModel = $customerModel;
        $this->mpHelper = $mpHelper;
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
        $loginUrl = $this->customerModel->getLoginUrl();

        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * loads custom layout according to condition
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $isPartner = $this->mpHelper->isSeller();
            if ($isPartner == 1
                || $this->helper->getConfigFeeAppliedFor() == Feeapplied::PER_VENDOR
            ) {
                if ($this->helper->isModuleEnabled()) {
                    $redirectSellerPanel = $this->checkRedirectPanel();
                    if ($redirectSellerPanel) {
                        return $this->setLayoutHandle();
                    } else {
                        $resultPage = $this->resultPageFactory->create();
                        if ($this->mpHelper->getIsSeparatePanel()) {
                            $resultPage->addHandle('mpmembership_layout2_product_form');
                        } else {
                            $resultPage->addHandle('mpmembership_product_submitform');
                        }
                        $resultPage->getConfig()->getTitle()->set(__('Seller Fee Payment Panel'));
                        return $resultPage;
                    }
                } else {
                    $this->_redirect('404');
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    'marketplace/account/becomeseller',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Index_execute Exception : ".$e->getMessage()
            );
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
    /**
     * [checkRedirectPanel]
     * @return bool
     */
    private function checkRedirectPanel()
    {
        if ($this->getRequest()->isPost()) {
            $params = $this->getRequest()->getParams();
            $flag = $this->helper->authenticateData($params);
            if ($flag) {
                $redirectSellerPanel = false;
            } else {
                $redirectSellerPanel = true;
                if (!empty($data['business']) && !empty($data['product_id'])) {
                    $this->messageManager->addError(
                        "Something went wrong !!!"
                    );
                }
            }
        } else {
            $redirectSellerPanel = true;
        }
        return $redirectSellerPanel;
    }
    /**
     * [setLayoutHandle which will shoe on frontend]
     */
    private function setLayoutHandle()
    {
        $fee = $this->helper->getConfigFeeAppliedFor();
        if ($fee == Feeapplied::PER_PRODUCT) {
            $resultPage = $this->resultPageFactory->create();
            if ($this->mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpmembership_layout2_product_index');
            } else {
                $resultPage->addHandle('mpmembership_product_index');
            }
            $resultPage->getConfig()->getTitle()->set(__('Seller Fee Payment Panel'));
            return $resultPage;
        } else {
            $resultPage = $this->resultPageFactory->create();
            if ($this->mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpmembership_layout2_seller_index');
            } else {
                $resultPage->addHandle('mpmembership_seller_index');
            }
            $resultPage->getConfig()->getTitle()->set(__('Seller Fee Payment Panel'));
            return $resultPage;
        }
    }
}
