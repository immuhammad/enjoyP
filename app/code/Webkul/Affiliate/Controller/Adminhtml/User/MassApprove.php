<?php
/**
 * Webkul Affiliate User Mass Approve Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\User;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Affiliate\Model\ResourceModel\User\CollectionFactory;
use Webkul\Affiliate\Helper\Email as HelperEmail;

class MassApprove extends \Magento\Backend\App\Action
{
    /**
     * Massactions for approve sales filter.
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var HelperEmail
     */
    protected $helperEmail;

    /**
     * @param Context            $context
     * @param Filter             $filter
     * @param CollectionFactory  $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        HelperEmail $helperEmail
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->helperEmail = $helperEmail;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $recordUpdate = 0;
            foreach ($collection as $affiUser) {
                if ($affiUser->getEnable() == 0 || $affiUser->getEnable() == 2) {
                    $affiUser->setEnable(1);
                    $this->_saveObject($affiUser);
                    $recordUpdate++;

                    /** send account approve mail notification to Affiliate User*/
                    $this->helperEmail->accountUpdateNotify($affiUser->getCustomerId(), __('Approved'));
                }
            }
            $this->messageManager->addSuccess(__('A total of %1 users(s) have been approved.', $recordUpdate));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index/');
    }

    /**
     * Check Affiliate Sales Approve Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::affiliate_user');
    }

    /**
     * saveObject
     * @param Object $object
     * @return void
     */

    protected function _saveObject($object)
    {
        $object->save();
    }
}
