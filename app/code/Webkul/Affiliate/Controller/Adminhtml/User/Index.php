<?php
/**
 * Webkul Affiliate User Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;
    private $scopeConfig;
    protected $_timezoneInterface;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_timezoneInterface = $timezoneInterface;
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Affiliate User List page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $payDate = $this->scopeConfig->getValue('affiliate/general/pay_date');
        $today = $this->_timezoneInterface->date(new \DateTime())->format('d');
        if ($payDate==$today) {
            $this->messageManager->addNotice(__('Today is the payment day.'));
        }
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Webkul_Affiliate::manage_user');
        $resultPage->getConfig()->getTitle()->prepend(__('Affiliate User List'));
        return $resultPage;
    }

    /**
     * Check Affiliate User List Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_user');
    }
}
