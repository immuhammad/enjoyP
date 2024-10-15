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

namespace Webkul\Mpquotesystem\Controller\Adminhtml\Managequotes;

use Webkul\Mpquotesystem\Controller\Adminhtml\Managequotes as Managequotes;
use Magento\Framework\Controller\ResultFactory;

class Index extends Managequotes
{
    /**
     * Set title
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $quoteCollection = $this->_mpquote->create()
        ->getCollection()
        ->addFieldToFilter('admin_pending_notification', ['neq' => 0]);

        if ($quoteCollection->getSize()) {
            $this->_updateNotification($quoteCollection);
        }
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Mpquotesystem::mpquotes');
        $resultPage->getConfig()->getTitle()->prepend(
            __('Marketplace Quote Manager')
        );
        $resultPage->addBreadcrumb(
            __('Marketplace Quote Manager'),
            __('Marketplace Quote Manager')
        );
        return $resultPage;
    }

    /**
     * Updated all notification as read.
     *
     * @param \Webkul\Mpquotesystem\Model\Quotes $collection
     */
    protected function _updateNotification($collection)
    {
        foreach ($collection as $value) {
            $value->setAdminPendingNotification(0);
            $value->save();
        }
    }
}
