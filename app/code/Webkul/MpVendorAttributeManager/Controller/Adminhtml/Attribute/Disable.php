<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Attribute;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

class Disable extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        $this->filter = $filter;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::manage');
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $vendorAttributeCollection = $this->filter->getCollection(
            $this->vendorAttributeCollectionFactory->create()
        );
        
        $count = 0;
        foreach ($vendorAttributeCollection as $vendorAttribute) {
            $vendorAttribute->setWkAttributeStatus(0);
            $this->saveObject($vendorAttribute);
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) Status have been Disabled.', $count));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save Object
     *
     * @param Object $object
     */
    protected function saveObject($object)
    {
        $object->save();
    }
}
