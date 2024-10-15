<?php
/**
 * Webkul Affiliate User Sales controller.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\User;

class Sales extends \Webkul\Affiliate\Controller\User\AbstractUser
{
    /**
     * Affiliate Sales
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        $resultPage = $this->getResultPageFactory()->create();
        $resultPage->getConfig()->getTitle()->set(__('Sales'));
        return $resultPage;
    }
}
