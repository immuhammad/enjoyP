<?php

/**
 * Webkul_Affiliate CheckIsRegisterAsAffiliate plugin
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Block\Plugin;

use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Backend\App\Action\Context;
use Magento\Customer\Model\Session;
use \Webkul\Affiliate\Helper\Data;

class CheckIsRegisterAsAffiliate
{
    /**
     * @var RedirectFactory
     */
    private $redirect;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var Session
     */
    private $session;

    private $helper;
    /**
     * @param RedirectFactory  $redirect,
     * @param Context          $context,
     * @param Session          $session,
     */
    public function __construct(
        RedirectFactory $redirect,
        Context $context,
        Session $session,
        Data $helper
    ) {
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->session = $session;
        $this->context = $context;
    }

    /**
     * redirect to affiliate user registration page if user not accept affiliate terms
     */
    public function aroundExecute(
        $subject,
        $procede,
        $data = "null",
        $requestInfo = false
    ) {
        $postData = $this->context->getRequest()->getPostValue();
        $flag=0;
        if (isset($postData['aff'])) {
            if (isset($postData['aff_conf'])) {
                if ($postData['aff_conf']!=1) {
                    $flag=1;
                } else {
                    if (!$this->helper->getConfigDataBlogLink()
                        || ($this->helper->getConfigDataBlogLink()
                        && $postData['bloglink'])
                    ) {
                        $flag=0;
                    } else {
                        $flag=1;
                    }
                }
            } else {
                $flag=1;
            }
        } else {
            $flag=0;
        }
        if ($flag==0) {
            return $procede();
        } else {
            $this->session->setCustomerFormData($this->context->getRequest()->getPostValue());
            $this->context->getMessageManager()
                          ->addError(__('Please accept affiliate terms for register as affiliate user.'));
            $resultRedirect = $this->redirect->create();
            $resultRedirect->setPath('*/*/create', ['aff'=>1]);
            return $resultRedirect;
        }
    }
}
