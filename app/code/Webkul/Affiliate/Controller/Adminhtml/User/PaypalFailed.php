<?php
/**
 * Webkul Affiliate User PaypalFailed
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Framework\Controller\ResultFactory;

class PaypalFailed extends \Magento\Backend\App\Action
{
    /**
     * Affiliate User PaypalSuccess
     *
     * @return \Magento\Framework\Controller\ResultFactory
     */
    public function execute()
    {
        $this->messageManager->addError(__('your payment has been declined.'));
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('affiliate/user/index/');
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_user');
    }
}
