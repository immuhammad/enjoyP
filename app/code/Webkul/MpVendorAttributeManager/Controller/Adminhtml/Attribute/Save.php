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
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;
use Webkul\MpVendorAttributeManager\Helper\Data;

class Save extends Action
{
    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $_eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $_attributeSetFactory;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $_attributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $vendorAssignGroupFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Config $eavConfig
     * @param AttributeSetFactory $attributeSetFactory
     * @param AttributeFactory $attributeFactory
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        AttributeFactory $attributeFactory,
        VendorAttributeFactory $vendorAttributeFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->_eavConfig = $eavConfig;
        $this->_attributeSetFactory = $attributeSetFactory;
        $this->_attributeFactory = $attributeFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_MpVendorAttributeManager::index');
    }

    /**
     * Save Custom Customer Attribute Action.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $formData = $this->getRequest()->getParams();
            $resultRedirect = $this->resultRedirectFactory->create();
            $redirectBack = $this->getRequest()->getParam('back', false);

            if ($formData) {
                $attributeCode = $this->getRequest()->getParam('attribute_code');
                $attributeId = $this->getRequest()->getParam('attribute_id');
                $id = '';
                if (!$attributeId) {
                    $attributeCode = "wkv_".$attributeCode;
                    if (!$this->validateAttributeCode($attributeCode)) {
                        return $resultRedirect->setPath('*/*/');
                    }
                    $vendorAttribute = $this->createAttribute();
                } else {
                    $vendorAttribute = $this->updateAttribute($attributeId);
                }
                /* Assign Attribute to vendor group */
                $this->assignVendorGroup($vendorAttribute->getAttributeId());
                $this->messageManager->addSuccess(__('You saved the vendor attribute.'));

                $attribute = $this->_attributeFactory->create()->load($vendorAttribute->getId());

                if ($redirectBack) {
                    $saveAndContinueId = $vendorAttribute->getId();
                    $this->_getSession()->setFormData($formData);
                    return $resultRedirect->setPath('*/*/edit', [
                        'id' => $saveAndContinueId,
                        'attribute_code' => $attribute->getAttributeCode() ,
                        '_current' => true
                    ]);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * Create new customer attribute and new record for Vendor Attribute
     *
     * @param Int $attributeSetId
     *
     * @return Object $vendorAttribute
     */
    protected function createAttribute()
    {
        $formData = $this->getRequest()->getPostValue();
        $vendorAttribute = $this->vendorAttributeFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        $customerEntity = $this->_eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet       = $this->_attributeSetFactory->create();
        $attributeGroupId   = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeCode = $this->getRequest()->getParam('attribute_code');
        $attributeCode  = "wkv_".$attributeCode;
        $attribute      = $this->_eavConfig->getAttribute('customer', $attributeCode);

        $attribute->addData(
            $this->getDefaultEntities(
                $formData['frontend_input'],
                $attributeSetId,
                $attributeGroupId
            )
        );

        $attribute->save();

        /** save record for webkul attribute manager table **/
        $vendorAttribute->setAttributeId($attribute->getId());
        $vendorAttribute->setRequiredField((int) (!empty($formData['is_required']) ? $formData['is_required'] : 0));
        $vendorAttribute->setAttributeUsedFor($formData['attribute_used_for']);
        $vendorAttribute->setWkAttributeStatus($formData['wk_attribute_status']);
        $vendorAttribute->setShowInFront(0);
        $vendorAttribute->save();

        return $vendorAttribute;
    }

    /**
     * Create new customer attribute and new record for Vendor Attribute
     *
     * @param Int $attributeId
     *
     * @return Object $vendorAttribute
     */
    protected function updateAttribute($attributeId)
    {
        $formData = $this->getRequest()->getPostValue();
        $attribute = $this->_attributeFactory->create();
        $resultRedirect = $this->resultRedirectFactory->create();

        $attribute->load($attributeId);
        if (!($attribute->getAttributeId())) {
            $this->messageManager->addError(__('This attribute no longer exists.'));
            return $resultRedirect->setPath('*/*/');
        }
        $formData['attribute_code'] = $attribute->getAttributeCode();
        $formData['frontend_input'] = $attribute->getFrontendInput();

        /* if attribute type is textarea and wysiwyg is enabled  */
        if (!isset($formData['frontend_class'])) {
            $formData['frontend_class'] = "";
        }

        if (($attribute->getFrontendInput() == 'textarea')) {
            $formData['frontend_class'] = '';
            if (isset($formData['is_wysiwyg_enabled']) && ($formData['is_wysiwyg_enabled'] == 1)) {
                $formData['frontend_class'] = 'wysiwyg_enabled';
            }
        }

        /* if attribute is required */
        if (isset($formData['is_required']) && ($formData['is_required'] == 1)) {
            $formData['frontend_class'] .= ' required';
        }

        $formData['is_required'] = isset($formData['is_required'])?$formData['is_required']:0;
        $customData['is_required'] = $formData['is_required'];
        $formData['is_required'] = 0;
        $attribute->addData($formData);
        $attribute->save();

        $vendorAttribute = $this->vendorAttributeFactory->create()->load($attributeId, 'attribute_id');
        $vendorAttribute->setRequiredField((int) $customData['is_required']);
        $vendorAttribute->setAttributeUsedFor($formData['attribute_used_for']);
        $vendorAttribute->setWkAttributeStatus($formData['wk_attribute_status']);
        $vendorAttribute->save();

        return $vendorAttribute;
    }

    /**
     * Validate Attribute Code in case of New Attribute Record Save
     *
     * @param String $attributeCode
     *
     * @return Bool True|False
     */
    protected function validateAttributeCode($attributeCode)
    {
        $pattern = '/^[a-z][a-z_0-9]{0,25}$/';

        if (strlen($attributeCode) < 0 || !preg_match($pattern, $attributeCode)) {
            $this->messageManager->addError(
                __(
                    'Attribute name "%1" is invalid. Please use only letters (a-z), numbers (0-9) '.
                    'or underscore(_) in this field and the first character should be a letter.',
                    $attributeCode
                )
            );
            return false;
        }

        // check for attribute code pre-exists or not.
        $attribute  = $this->_eavConfig->getAttribute('customer', $attributeCode);
        $collection = $attribute->getCollection()
                    ->addFieldToFilter('attribute_code', $attributeCode);
        if ($collection->getSize() > 0) {
            $this->messageManager->addError(__('The attribute ID already exists.'));
            return false;
        }

        return true;
    }

    /**
     * Retrieve default entities: customer custom attribute
     *
     * @param string $field
     * @param int $attributeSetId
     * @param int $attributeGroupId
     * @return array
     */
    protected function getDefaultEntities($field, $attributeSetId, $attributeGroupId)
    {
        $formData = $this->getRequest()->getPostValue();

        $formData['frontend_class'] = $this->setFrontendClass($field, $formData);

        $backendForms = ['adminhtml_customer'];

        $entities = [
            'frontend_input'        => $field,
            'is_system'             => false,
            'is_user_defined'       => true,
            'attribute_set_id'      => $attributeSetId,
            'attribute_group_id'    => $attributeGroupId,
            'used_in_forms'         => $backendForms,
            'frontend_label'        => $formData['frontend_label'],
            'frontend_class'        => $formData['frontend_class'],
            'sort_order'            => $formData['sort_order'],
            'position'              => $formData['sort_order'],
            'is_visible'            => false,
        ];

        switch ($field) {
            case "date":
                $entities['frontend_type']  = 'datetime';
                $entities['backend_type']   = 'datetime';
                $entities['frontend_model']  = \Magento\Eav\Model\Entity\Attribute\Frontend\Datetime::class;
                $entities['backend_model']  = \Magento\Eav\Model\Entity\Attribute\Backend\Datetime::class;
                $entities['validate_rules'] = '{"input_validation":"date"}';
                break;
            case "select":
                $entities['frontend_type']  = 'varchar';
                $entities['backend_type']   = 'varchar';
                $entities['source_model']   =  \Magento\Eav\Model\Entity\Attribute\Source\Table::class;
                $entities['option']         = isset($formData['option']) ? $formData['option'] : [];
                break;
            case "multiselect":
                $entities['frontend_type']  = 'varchar';
                $entities['backend_type']   = 'varchar';
                $entities['backend_model']  =  \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class;
                $entities['source_model']  =  \Magento\Eav\Model\Entity\Attribute\Source\Table::class;
                $entities['option']         = isset($formData['option']) ? $formData['option'] : [];
                break;
            case "boolean":
                $entities['frontend_type']  = 'int';
                $entities['backend_type']   = 'int';
                $entities['backend_model']  = \Magento\Customer\Model\Attribute\Backend\Data\Boolean::class;
                break;
            case "image":
                $entities['frontend_type']  = 'varchar';
                $entities['backend_type']   = 'varchar';
                $entities['backend_model']  = \Magento\Eav\Model\Entity\Attribute\Backend\DefaultBackend::class;
                break;
            case "file":
                $entities['frontend_type']  = 'varchar';
                $entities['backend_type']   = 'varchar';
                $entities['backend_model']  = \Magento\Eav\Model\Entity\Attribute\Backend\Increment::class;
                break;
            case "textarea":
                $entities['frontend_type'] = 'varchar';
                $entities['backend_type'] = 'text';
                break;
            default: // for text and textarea
                $entities['frontend_type'] = 'varchar';
                $entities['backend_type'] = 'varchar';
        }

        return $entities;
    }

    /**
     * Set Frontend Class for Customer Attribute Form Data
     *
     * @param String $field
     * @param Array $formData
     *
     * @return String $frontendClass
     */
    public function setFrontendClass($field, $formData)
    {
        $frontendClass = "";

        if (isset($formData['frontend_class'])) {
            $frontendClass = $formData['frontend_class'];
        }

        if ($field == "textarea" && !empty($formData['is_wysiwyg_enabled'])) {
            $frontendClass .= ' wysiwyg_enabled';
        }

        if (isset($formData['is_required']) && ($formData['is_required'] == 1)) {
            $frontendClass .= " required";
        }

        return $frontendClass;
    }

    /**
     * Assign Attribute to Vendor Group
     *
     * @param int $attributeId
     */
    protected function assignVendorGroup($attributeId)
    {
        $formData = $this->getRequest()->getPostValue();
        $vendorAssignGroupModel = $this->vendorAssignGroupFactory->create();

        $vendorAssignGroupCollection = $vendorAssignGroupModel->getCollection()
                                                ->addFieldToFilter('attribute_id', ['eq' => $attributeId]);
        if ($vendorAssignGroupCollection->getSize()) {
            foreach ($vendorAssignGroupCollection as $vendorAssignGroup) {
                $this->deleteObject($vendorAssignGroup);
            }
        }

        if (!empty($formData['assign_group'])) {
            $groupId = $formData['assign_group'];
            $vendorAssignGroupCollection = $vendorAssignGroupModel->getCollection()
                                            ->addFieldToFilter('attribute_id', ['eq' => $attributeId])
                                            ->addFieldToFilter('group_id', ['eq' => $groupId]);

            if (!$vendorAssignGroupCollection->getSize()) {
                $vendorAssignGroup = $this->vendorAssignGroupFactory->create();
                $vendorAssignGroup->setAttributeId($attributeId);
                $vendorAssignGroup->setGroupId($groupId);
                $this->saveObject($vendorAssignGroup);
            }
        }
    }

    /**
     * Save Object
     *
     * @param object $object
     */
    protected function saveObject($object)
    {
        $object->save();
    }

    /**
     * Delete Object
     *
     * @param object $object
     */
    protected function deleteObject($object)
    {
        $object->delete();
    }
}
