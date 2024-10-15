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

use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;

/**
 * Class AssignGroup
 *
 * Webkul\MpVendorAttributeManager\Controller\Adminhtml\Attribute
 */
class AssignGroup extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $assignModelFactory;
    
    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param VendorAssignGroupFactory $assignModelFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        VendorAssignGroupFactory $assignModelFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->assignModelFactory = $assignModelFactory;
        parent::__construct($context);
    }
    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization
        ->isAllowed(
            'Webkul_MpVendorAttributeManager::index'
        );
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $groupId = $this->getRequest()->getParam('entity_id');

        if (!empty($groupId)) {
            $vendorAttributes = $collection->getColumnValues("attribute_id");
            $vendorAssignGroupCollection = $this->assignModelFactory->create()->getCollection()
                                                ->addFieldToFilter("attribute_id", ['in' => $vendorAttributes]);

            foreach ($vendorAssignGroupCollection as $vendorAssignGroup) {
                $this->deleteObject($vendorAssignGroup);
            }

            foreach ($collection as $item) {
                $assignModel = $this->assignModelFactory->create();
                $assignCollection = $assignModel->getCollection()
                    ->addFieldToFilter('attribute_id', ['eq' => $item->getAttributeId()])
                    ->addFieldToFilter('group_id', ['eq' => $groupId]);
                if (!$assignCollection->getSize()) {
                    if (empty($groupId)) {
                        break;
                    }
                    $assignModel->setAttributeId($item->getAttributeId());
                    $assignModel->setGroupId($groupId);
                    $this->saveObject($assignModel);
                }
            }

            $this->messageManager->addSuccess(__(
                'A total of %1 record(s) have been assigned.',
                $collection->getSize()
            ));
        } else {
            $this->messageManager->addWarning(__('No group found.'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
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

    /**
     * Delete Object
     *
     * @param Object $object
     */
    protected function deleteObject($object)
    {
        $object->delete();
    }
}
