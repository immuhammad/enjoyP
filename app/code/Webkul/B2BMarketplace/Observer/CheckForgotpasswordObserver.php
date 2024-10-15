<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Observer;

class CheckForgotpasswordObserver extends \Magento\Captcha\Observer\CheckForgotpasswordObserver
{
    /**
     * @var \Magento\Captcha\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\App\ActionFlag
     */
    private $actionFlag;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $_redirect;

    /**
     * @var \Magento\Captcha\Observer\CaptchaStringResolver
     */
    protected $_captchaStringResolver;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    private $response;

    /**
     * @var \Webkul\Sso\Helper\Data
     */
    private $helperData;

    /**
     * __construct function
     *
     * @param \Magento\Captcha\Helper\Data                      $helper
     * @param \Magento\Framework\App\ActionFlag                 $actionFlag
     * @param \Magento\Framework\Message\ManagerInterface       $messageManager
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Captcha\Observer\CaptchaStringResolver   $captchaStringResolver
     * @param \Magento\Framework\UrlInterface                   $urlBuilder
     * @param \Magento\Framework\App\ResponseInterface          $response
     */
    public function __construct(
        \Magento\Captcha\Helper\Data $helper,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Captcha\Observer\CaptchaStringResolver $captchaStringResolver,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\ResponseInterface $response
    ) {
        parent::__construct($helper, $actionFlag, $messageManager, $redirect, $captchaStringResolver);
        $this->helper = $helper;
        $this->actionFlag = $actionFlag;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->_captchaStringResolver = $captchaStringResolver;
        $this->urlBuilder =  $urlBuilder;
        $this->response =  $response;
    }

    /**
     * Check Captcha On Forgot Password Page
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $formId = 'user_forgotpassword';
        $captchaModel = $this->helper->getCaptcha($formId);
        if ($captchaModel->isRequired()) {
            /** @var \Magento\Framework\App\Action\Action $controller */
            $controller = $observer->getControllerAction();
            $post = $controller->getRequest()->getParams();
            if (!$captchaModel->isCorrect($this->_captchaStringResolver->resolve($controller->getRequest(), $formId))) {
                $this->messageManager->addError(__('Incorrect CAPTCHA'));
                $this->actionFlag->set('', \Magento\Framework\App\Action\Action::FLAG_NO_DISPATCH, true);
                if (isset($post['module']) && $post['module'] == 'webkul_b2bmarketplace') {
                    $ssoRedirectUrl= $this->urlBuilder->getUrl("b2bmarketplace/supplier/login/");
                    $this->_redirect->redirect($this->response, $ssoRedirectUrl);
                } else {
                    $this->_redirect->redirect($controller->getResponse(), '*/*/forgotpassword');
                }
            }
        }
        return $this;
    }
}
