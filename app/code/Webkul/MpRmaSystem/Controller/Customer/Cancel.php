<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Controller\Customer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Webkul\MpRmaSystem\Helper\Data;

class Cancel extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $session;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * @var \Webkul\MpRmaSystem\Model\DetailsFactory
     */
    protected $details;

    /**
     * Initialize Dependencies
     *
     * @param Context $context
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param \Webkul\MpRmaSystem\Model\DetailsFactory $details
     * @return void
     */
    public function __construct(
        Context $context,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        \Webkul\MpRmaSystem\Model\DetailsFactory $details
    ) {
        $this->url         = $url;
        $this->session     = $session;
        $this->mpRmaHelper = $mpRmaHelper;
        $this->details     = $details;
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
        $loginUrl = $this->url->getLoginUrl();
        if (!$this->session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }

        return parent::dispatch($request);
    }

    /**
     * Cancel Rma Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $rmaId = $this->getRequest()->getParam("id");
        $helper = $this->mpRmaHelper;
        if (!$helper->isValidRma(1)) {
            $this->messageManager->addError(__("Invalid Request"));
        } else {
            $rmaData = ['status' => Data::RMA_STATUS_CANCELED, 'final_status' => Data::FINAL_STATUS_CANCELED];
            $rma = $this->details->create()->load($rmaId);
            $rma->addData($rmaData)->setId($rmaId)->save();
            $this->messageManager->addSuccess(__("RMA request canceled."));
            $helper->sendUpdateRmaEmail(['rma_id' => $rmaId]);
        }

        return $this->resultRedirectFactory->create()->setPath('*/*/allrma');
    }
}
