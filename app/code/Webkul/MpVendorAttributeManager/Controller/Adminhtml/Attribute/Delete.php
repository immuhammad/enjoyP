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
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $_filter;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $vendorAttributeCollectionFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param AttributeFactory $attributeFactory
     * @param CollectionFactory $vendorAttributeCollectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AttributeFactory $attributeFactory,
        CollectionFactory $vendorAttributeCollectionFactory
    ) {
        parent::__construct($context);
        $this->_filter = $filter;
        $this->_attributeFactory = $attributeFactory;
        $this->_vendorAttributeCollectionFactory = $vendorAttributeCollectionFactory;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
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
            $this->_vendorAttributeCollectionFactory->create()
        );
        $count = 0;
        foreach ($vendorAttributeCollection as $vendorAttribute) {
            $attributeModel = $this->loadAttributeById($vendorAttribute->getAttributeId());
            $this->deleteObject($attributeModel);
            $this->deleteObject($vendorAttribute);
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $count));

        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Load Attribute Model by Id
     *
     * @param Int $id
     *
     * @return Object $attributeModel
     */
    protected function loadAttributeById($id)
    {
        $attributeModel = $this->_attributeFactory->create()->load($id);
        return $attributeModel;
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
