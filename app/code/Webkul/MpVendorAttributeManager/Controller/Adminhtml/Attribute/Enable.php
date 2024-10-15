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

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory;

class Enable extends Action
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
     * Constuctor
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
        $this->_filter = $filter;
        $this->_attributeFactory = $attributeFactory;
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
        $attributeCollection = $this->_filter->getCollection(
            $this->vendorAttributeCollectionFactory->create()
        );

        $count = 0;
        foreach ($attributeCollection as $attribute) {
            $attribute->setWkAttributeStatus(1);
            $this->saveObject($attribute);
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) Status has been Enabled.', $count));

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
