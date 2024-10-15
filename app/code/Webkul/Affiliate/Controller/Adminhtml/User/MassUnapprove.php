<?php
/**
 * Webkul Affiliate User Mass Unapprove Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Framework\Controller\ResultFactory;

class MassUnapprove extends \Webkul\Affiliate\Controller\Adminhtml\User\MassApprove
{
    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $recordUpdate = 0;
            foreach ($collection as $affiUser) {//echo "<pre>";print_r($affiUser->getData());exit;
                if ($affiUser->getEnable() == 1) {
                    $affiUser->setEnable(2);
                    $this->_saveObject($affiUser);
                    $recordUpdate++;

                    /** send account unapprove mail notification to Affiliate User*/
                    $this->helperEmail->accountUpdateNotify($affiUser->getCustomerId(), 'Unapproved');
                }
                $this->_saveObject($affiUser);
            }

            $this->messageManager->addSuccess(__('A total of %1 users(s) have been rejected.', $recordUpdate));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index/');
    }
}
