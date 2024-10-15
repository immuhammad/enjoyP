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
declare(strict_types=1);

namespace Webkul\MpVendorAttributeManager\Model\Resolver\Admin;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;

/**
 * SaveMpVendorAttribute resolver, used for GraphQL request processing
 */
class SaveMpVendorAttribute implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Customer\Model\AttributeFactory
     */
    protected $attributeFactory;

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
     * @var mixed
     */
    private $inputData;

    /**
     * Constructor
     *
     * @param Config $eavConfig
     * @param AttributeSetFactory $attributeSetFactory
     * @param AttributeFactory $attributeFactory
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory,
        AttributeFactory $attributeFactory,
        VendorAttributeFactory $vendorAttributeFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->eavConfig = $eavConfig;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->attributeFactory = $attributeFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->helper = $helper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        /** @var ContextInterface $context */
        if ($context->getUserType() != self::ADMIN_USER_TYPE) {
            throw new GraphQlAuthorizationException(__('Unauthorized access. Only admin can access this information.'));
        }

        try {
            if (empty($args['input']) || !is_array($args['input'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('"input" value should be specified')
                );
            }

            $this->inputData = $this->getFormattedInput($args['input']);
            $attributeCode = $this->inputData['attribute_code'] ?? '';
            $attributeId = $this->inputData['attribute_id'] ?? '';

            if (!$attributeId) {
                $attributeCode = "wkv_".$attributeCode;
                $this->validateAttributeCode($attributeCode);
                
                $vendorAttribute = $this->createAttribute();
            } else {
                $vendorAttribute = $this->updateAttribute($attributeId);
            }
            /* Assign Attribute to vendor group */
            $this->assignVendorGroup($vendorAttribute->getAttributeId());
            $returnArray['message'] = __('You saved the vendor attribute.');
            $returnArray['status'] = self::SUCCESS;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\RuntimeException $e) {
            $returnArray['message'] = $e->getMessage();
            $returnArray['status'] = self::LOCAL_ERROR;
        } catch (\Exception $e) {
            $returnArray['message'] = __('Invalid Request');
            $returnArray['status'] = self::SEVERE_ERROR;
        }
        return $returnArray;
    }

    /**
     * Function get Formatted Input
     *
     * @param array $inputData
     * @return void
     */
    protected function getFormattedInput($inputData)
    {
        $formattedLabel = [];
        if (!empty($inputData['frontend_label'])) {
            $frontendLabelArray = $inputData['frontend_label'];
            foreach ($frontendLabelArray as $frontendLabel) {
                $formattedLabel[$frontendLabel['store_id']] = $frontendLabel['value'];
            }
        }
        $inputData['frontend_label'] = $formattedLabel;

        $formattedOption = [];
        if (!empty($inputData['option'])) {
            $optionArray = $inputData['option'];

            $orderArray = $optionArray['order'] ?? [];
            $formattedOrder = [];
            $count = 0;
            foreach ($orderArray as $order) {
                if (!empty($order['option_id'])) {
                    $formattedOrder[$order['option_id']] = $order['value'];
                } else {
                    $formattedOrder['option_'.$count] = $order['value'];
                }
                $count++;
            }

            $valueArray = $optionArray['value'] ?? [];
            $formattedValue = [];
            $count = 0;
            foreach ($valueArray as $valueData) {
                foreach ($valueData['value'] as $value) {
                    if (!empty($valueData['option_id'])) {
                        $formattedValue[$valueData['option_id']][$value['store_id']] = $value['value'];
                    } else {
                        $formattedValue['option_'.$count][$value['store_id']] = $value['value'];
                    }
                }
                $count++;
            }

            $deleteArray = $optionArray['delete'] ?? [];
            $formattedDelete = [];
            $count = 0;
            foreach ($deleteArray as $delete) {
                if (!empty($delete['option_id'])) {
                    $formattedDelete[$delete['option_id']] = $delete['value'];
                } else {
                    $formattedDelete['option_'.$count] = $delete['value'];
                }
                $count++;
            }

            $formattedOption = [
                'order' => $formattedOrder,
                'value' => $formattedValue,
                'delete' => $formattedDelete
            ];
        }
        $inputData['option'] = $formattedOption;
        return $inputData;
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
        $formData = $this->inputData;
        $vendorAttribute = $this->vendorAttributeFactory->create();

        $customerEntity = $this->eavConfig->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        $attributeSet       = $this->attributeSetFactory->create();
        $attributeGroupId   = $attributeSet->getDefaultGroupId($attributeSetId);

        $attributeCode = $this->inputData['attribute_code'];
        $attributeCode  = "wkv_".$attributeCode;
        $attribute      = $this->eavConfig->getAttribute('customer', $attributeCode);

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
        $formData = $this->inputData;
        $attribute = $this->attributeFactory->create();

        $attribute->load($attributeId);
        if (!($attribute->getAttributeId())) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This attribute no longer exists.')
            );
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
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'Attribute name "%1" is invalid. Please use only letters (a-z), numbers (0-9) '.
                    'or underscore(_) in this field and the first character should be a letter.',
                    $attributeCode
                )
            );
        }

        // check for attribute code pre-exists or not.
        $attribute  = $this->eavConfig->getAttribute('customer', $attributeCode);
        $collection = $attribute->getCollection()
                    ->addFieldToFilter('attribute_code', $attributeCode);
        if ($collection->getSize() > 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The attribute ID already exists.')
            );
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
        $formData = $this->inputData;

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
        $formData = $this->inputData;
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
