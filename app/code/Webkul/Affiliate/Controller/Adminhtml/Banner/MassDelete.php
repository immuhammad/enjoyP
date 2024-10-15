<?php
/**
 * Webkul Affiliate Banner Mass Delete Controller
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Controller\Adminhtml\Banner;

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Affiliate\Model\ResourceModel\TextBanner\CollectionFactory;

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Massactions for approve sales filter.
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param Context            $context
     * @param Filter             $filter
     * @param CollectionFactory  $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
    
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $recordDelete = 0;
            foreach ($collection as $affiUser) {
                $this->_deleteObject($affiUser);
                $recordDelete++;
            }
            $this->messageManager->addSuccess(__('A total of %1 banner(s) have been deleted.', $recordDelete));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index/');
    }

    /**
     * Check Affiliate Banner Delete Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Affiliate::banner_manage_text_ads');
    }

    /**
     * saveObject
     * @param Object $object
     * @return void
     */

    private function _deleteObject($object)
    {
        $object->delete();
    }
}
