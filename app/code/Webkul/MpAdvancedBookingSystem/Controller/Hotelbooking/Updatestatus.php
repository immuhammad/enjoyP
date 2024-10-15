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
namespace Webkul\MpAdvancedBookingSystem\Controller\Hotelbooking;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Customer\Model\Session;

class Updatestatus extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory
     */
    protected $questionModel;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Customer\Model\Url
     */
    private $customerUrl;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param Session $customerSession
     * @param \Magento\Customer\Model\Url $customerUrl
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedBookingSystem\Model\QuestionFactory $questionModel,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        Session $customerSession,
        \Magento\Customer\Model\Url $customerUrl
    ) {
        parent::__construct($context);
        $this->redirect = $context->getRedirect();
        $this->questionModel = $questionModel;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        $this->customerSession = $customerSession;
        $this->customerUrl = $customerUrl;
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
     * @return \Magento\Framework\Model\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->mpHelper->isSeller();
        if ($isPartner == 1) {
            $postItems = $this->getRequest()->getParams();
            $statuses = $this->helper->getQuestionStatuses();
            if (empty($postItems)) {
                $this->messageManager->addError(__('Please correct the data sent.'));
            } else {
                try {
                    $model = $this->questionModel->create()->load((int)$postItems['id']);
                    $model->setStatus($postItems['status'])->save();
                    $this->messageManager->addSuccess(__('Query has been %1.', $statuses[$postItems['status']]));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__($e->getMessage()));
                }
            }
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl($this->redirect->getRefererUrl());
            return $resultRedirect;
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
