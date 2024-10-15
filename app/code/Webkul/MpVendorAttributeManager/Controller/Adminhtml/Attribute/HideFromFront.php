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

class HideFromFront extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

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
        $this->_filter = $filter;
        $this->vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
        parent::__construct($context);
    }

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::index');
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $vendorAttributeCollection = $this->_filter->getCollection(
            $this->vendorAttributeCollectionFactory->create()
        );

        foreach ($vendorAttributeCollection as $vendorAttribute) {
            $vendorAttribute->setShowInFront(0);
            $this->saveObject($vendorAttribute);
        }

        $this->messageManager->addSuccess(
            __('A total of %1 record(s) have been disabled for Seller Profile.', $vendorAttributeCollection->getSize())
        );

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Save Object
     *
     * @param [type] $object
     * @return void
     */
    protected function saveObject($object)
    {
        $object->save();
    }
}
