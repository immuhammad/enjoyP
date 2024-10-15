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
use Magento\Framework\Controller\ResultFactory;

class MassUsedfor extends Action
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
    protected $_collectionFactory;
    
    /**
     * Constructor
     *
     * @param Context $context
     * @param Filter $filter
     * @param AttributeFactory $attributeFactory
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        AttributeFactory $attributeFactory,
        CollectionFactory $collectionFactory
    ) {
        parent::__construct($context);
        $this->_filter = $filter;
        $this->_attributeFactory = $attributeFactory;
        $this->_collectionFactory = $collectionFactory;
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
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        $vendorAttributeCollection = $this->_filter->getCollection($this->_collectionFactory->create());
        $attributeUsedFor = $this->getRequest()->getParam('type');
        
        $count = 0;

        $isVisible = 1;
        if ($attributeUsedFor == 2) {
            $isVisible = 0;
        }
        
        foreach ($vendorAttributeCollection as $vendorAttribute) {
            $attributeId = $vendorAttribute->getAttributeId();

            $attribute = $this->getAttribute($attributeId);
            $attribute->setIsVisible($isVisible);
            $this->saveModel($attribute);

            $vendorAttribute->setAttributeUsedFor($attributeUsedFor);
            $this->saveModel($vendorAttribute);
            
            $count++;
        }
        $this->messageManager->addSuccess(__('A total of %1 record(s) have been updated.', $count));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Get Attribute Model for Attribute Id
     *
     * @param Int $attributeId
     *
     * @return Model \Magento\Customer\Model\AttributeFactory $attribute
     */
    protected function getAttribute($attributeId)
    {
        return $this->_attributeFactory->create()->load($attributeId);
    }

    /**
     * Save Model
     *
     * @param Object $model
     */
    protected function saveModel($model)
    {
        $model->save();
    }
}
