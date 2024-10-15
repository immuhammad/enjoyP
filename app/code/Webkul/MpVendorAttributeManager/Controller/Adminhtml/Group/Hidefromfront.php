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
namespace Webkul\MpVendorAttributeManager\Controller\Adminhtml\Group;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;

class Hidefromfront extends Action
{
    /**
     * @var boolean
     */
    protected $_status = false;

    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorGroup\CollectionFactory
     */
    protected $vendorGroupCollectionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $vendorAssignGroupFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $vendorGroupCollectionFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $vendorGroupCollectionFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory
    ) {
        parent::__construct($context);
        $this->filter = $filter;
        $this->vendorGroupCollectionFactory = $vendorGroupCollectionFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::group');
    }

    /**
     * HideFromFront action
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        try {
            $vendorGroupCollection = $this->filter->getCollection(
                $this->vendorGroupCollectionFactory->create()
            );
            $count = 0;
            foreach ($vendorGroupCollection as $vendorGroup) {
                $vendorGroup->setStatus($this->_status);
                $this->saveObject($vendorGroup);
                $count++;
            }
            $this->messageManager->addSuccess(__('A total of %1 record(s) have been Saved.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/group/index');
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
