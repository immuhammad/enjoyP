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

namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking\Question;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;

/**
 * Webkul MpAdvancedBookingSystem Hotelbooking Answers controller.
 */
class Answers extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $helperData;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $helperData,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        $this->customerSession = $customerSession;
        $this->resultPageFactory = $resultPageFactory;
        $this->helperData = $helperData;
        $this->helper = $helper;
        $this->customerUrl = $customerUrl;
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
        $loginUrl = $this->customerUrl->getLoginUrl();

        if (!$this->customerSession->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $mpHelper = $this->helperData;
        $isPartner = $mpHelper->isSeller();
        $sellerDashboardUrl = $this->resultRedirectFactory->create()->setPath(
            'marketplace/account/dashboard',
            ['_secure' => $this->getRequest()->isSecure()]
        );
        if ($isPartner == 1) {
            $params = $this->getRequest()->getParams();
            if (empty($params['question_id'])) {
                $this->messageManager->addErrorMessage(
                    __("Something went wrong !!!")
                );
                return $this->resultRedirectFactory->create()->setPath(
                    '*/hotelbooking/questions',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
            if (!$this->helper->getMpConfigValue('enable_booking')) {
                return $sellerDashboardUrl;
            }
            $resultPage = $this->resultPageFactory->create();
            if ($mpHelper->getIsSeparatePanel()) {
                $resultPage->addHandle('mpadvancebooking_layout2_hotelbooking_answers');
            }
            $resultPage->getConfig()->getTitle()->set(
                __("Marketplace Hotel Booking Asked Question's Answers")
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
