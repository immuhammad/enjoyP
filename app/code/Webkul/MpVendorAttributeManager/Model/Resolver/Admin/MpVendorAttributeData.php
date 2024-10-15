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
use Magento\Eav\Model\Entity;
use Magento\Customer\Model\AttributeMetadataDataProviderFactory;
use Magento\Customer\Model\AttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory;
use Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory;
use Magento\Customer\Model\Customer;
use Webkul\MpVendorAttributeManager\Api\VendorAttributeRepositoryInterface;

/**
 * MpVendorAttributeData resolver, used for GraphQL request processing
 */
class MpVendorAttributeData implements ResolverInterface
{
    public const ADMIN_USER_TYPE = 2;

    public const SEVERE_ERROR = 0;
    public const SUCCESS = 1;
    public const LOCAL_ERROR = 2;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Customer\Model\AttributeMetadataDataProviderFactory
     */
    protected $attributeMetaData;

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
     * @var VendorAttributeRepositoryInterface
     */
    protected $vendorAttributeRepo;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * Constructor
     *
     * @param Entity $eavEntity
     * @param AttributeMetadataDataProviderFactory $attributeMetaData
     * @param AttributeFactory $attributeFactory
     * @param VendorAttributeFactory $vendorAttributeFactory
     * @param VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param VendorAttributeRepositoryInterface $vendorAttributeRepo
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param \Magento\Eav\Helper\Data $eavData
     * @param \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory
     * @param \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\VendorGroups $vendorGroups
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\AttributeUsedFor $usedFor
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\Status $attributeStatus
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options $attributeOptions
     * @param \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Labels $attributeLabels
     */
    public function __construct(
        Entity $eavEntity,
        AttributeMetadataDataProviderFactory $attributeMetaData,
        AttributeFactory $attributeFactory,
        VendorAttributeFactory $vendorAttributeFactory,
        VendorAssignGroupFactory $vendorAssignGroupFactory,
        VendorAttributeRepositoryInterface $vendorAttributeRepo,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        \Magento\Eav\Helper\Data $eavData,
        \Magento\Config\Model\Config\Source\YesnoFactory $yesnoFactory,
        \Magento\Eav\Model\Adminhtml\System\Config\Source\InputtypeFactory $inputTypeFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\VendorGroups $vendorGroups,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\AttributeUsedFor $usedFor,
        \Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source\Status $attributeStatus,
        \Magento\Framework\Registry $registry,
        \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Options $attributeOptions,
        \Magento\Eav\Block\Adminhtml\Attribute\Edit\Options\Labels $attributeLabels
    ) {
        $this->eavEntity = $eavEntity;
        $this->attributeMetaData = $attributeMetaData;
        $this->attributeFactory = $attributeFactory;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->vendorAttributeRepo = $vendorAttributeRepo;
        $this->helper = $helper;
        $this->eavData = $eavData;
        $this->yesnoFactory = $yesnoFactory;
        $this->inputTypeFactory = $inputTypeFactory;
        $this->vendorGroups = $vendorGroups;
        $this->usedFor = $usedFor;
        $this->attributeStatus = $attributeStatus;
        $this->coreRegistry = $registry;
        $this->attributeOptions = $attributeOptions;
        $this->attributeLabels = $attributeLabels;
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

        $this->entityTypeId = $this->eavEntity->setType(Customer::ENTITY)->getTypeId();
        $attributeModel = $this->attributeFactory->create()->setEntityTypeId($this->entityTypeId);

        $id = $args['id'] ?? 0;
        $class = '';
        $attributeData = [];
        if ($id) {
            // for checking if vendor attribute exists or not
            $vendorAttributeModel = $this->vendorAttributeRepo->getById($id);
            $attributeId = $vendorAttributeModel->getAttributeId();

            $attributeModel->load($vendorAttributeModel->getAttributeId());

            $class = $attributeModel->getFrontendClass() ?? '';
            $attributeModel->setIsVisible($vendorAttributeModel->getStatus());
            if (!$attributeModel->getId()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This attribute no longer exists.')
                );
            }

            if ($attributeModel->getEntityTypeId() != $this->entityTypeId) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('This attribute cannot be edited.')
                );
            }

            $requiredCheck = $attributeModel->getFrontendClass() ?? '';
            $require = explode(' ', $requiredCheck);
            if (in_array('required', $require)) {
                $attributeModel->setIsRequired(1);
                $attributeModel->setFrontendClass($require[0]);
            }
            /*
                If attribute assigned to groups
            */
            $vendorAssignGroupCollection = $this->vendorAssignGroupFactory->create()
                                            ->getCollection()
                                            ->addFieldToFilter('attribute_id', ['eq' =>
                                                $vendorAttributeModel->getAttributeId()]);
            $assignedGroups = $vendorAssignGroupCollection->getColumnValues('group_id');
            $attributeGroup = implode(',', $assignedGroups);

            $attributeModel->setFrontendClass($class);

            $label = $attributeModel->getFrontendLabel();
            $defaultLabel = is_array($label) ? $label[0] : $label;
            $attributeData = [
                'entity_id' => $id,
                'attribute_id' => $attributeId,
                'attribute_code' => $attributeModel->getAttributeCode(),
                'attribute_label' => $label,
                'frontend_input' => $attributeModel->getFrontendInput(),
                'is_required' => $attributeModel->getIsRequired(),
                'frontend_class' => $attributeModel->getFrontendClass() ?? '',
                'sort_order' => $attributeModel->getSortOrder(),
                'assign_group' => $attributeGroup,
                'attribute_used_for' => $vendorAttributeModel->getAttributeUsedFor(),
                'wk_attribute_status' => $vendorAttributeModel->getWkAttributeStatus()
            ];
        }

        $returnArray['attributeData'] = $attributeData;

        $this->coreRegistry->register('entity_attribute', $attributeModel);
        $returnArray['stores'] = $this->getStoresData();
        $returnArray['option'] = $this->getAttributeOption();
        $returnArray['frontend_label'] = $this->getAttributeFrontendLabel();

        $yesno = $this->yesnoFactory->create()->toOptionArray();

        // $attributeTypes = $this->inputTypeFactory->create()->toOptionArray();
        // toOptionArray function of $this->inputTypeFactory not giving the results for graphql api
        $attributeTypes = $this->getAttributeTypes();
        $notAllowed = ['texteditor', 'datetime', 'pagebuilder'];
        foreach ($attributeTypes as $attributeKey => $attributeData) {
            if (in_array($attributeData['value'], $notAllowed)) {
                unset($attributeTypes[$attributeKey]);
            }
        }

        $frontendClasses = $this->eavData->getFrontendClasses($attributeModel->getEntityType()->getEntityTypeCode());

        $vendorGroups = $this->vendorGroups->toOptionArray();

        $usedFor = $this->usedFor->toOptionArray();

        $wkAttributeStatus = [
            [
                'value' => '1',
                'label' => __('Enable')
            ],
            [
                'value' => '0',
                'label' => __('Disable')
            ]
            ];

        $optionsData = [
            'frontend_input' => $attributeTypes,
            'is_required' => $yesno,
            'frontend_class' => $frontendClasses,
            'assign_group' => $vendorGroups,
            'attribute_used_for' => $usedFor,
            'wk_attribute_status' => $wkAttributeStatus,
            'is_wysiwyg_enabled' => $yesno
        ];

        $returnArray['optionsData'] = $optionsData;

        return $returnArray;
    }

    /**
     * Function get Stores Data
     *
     * @return void
     */
    protected function getStoresData()
    {
        $optionsBlock = $this->attributeOptions;
        $stores = $optionsBlock->getStoresSortedBySortOrder();
        $storesArray = [];
        foreach ($stores as $value) {
            $storesArray[] = [
                'store_id' => $value->getStoreId(),
                'name' => $value->getName()
            ];
        }
        return $storesArray;
    }

    /**
     * Function get Attribute Option
     *
     * @return void
     */
    protected function getAttributeOption()
    {
        $optionsBlock = $this->attributeOptions;
        $optionArray = [];
        foreach ($optionsBlock->getOptionValues() as $value) {
            $value = $value->getData();
            $value = is_array($value) ? array_map("htmlspecialchars_decode", $value) : $value;

            $optionArray['order'][] = [
                'option_id' => $value['id'],
                'value' => $value['sort_order'],
            ];

            $valueArray = [];
            foreach ($value as $key => $data) {
                if (strpos($key, 'store') !== false) {
                    $valueArray[] = [
                        'store_id' => str_replace('store', '', $key),
                        'value' => $data
                    ];
                }
            }
            $optionArray['value'][] = [
                'option_id' => $value['id'],
                'value' => $valueArray
            ];
        }
        return $optionArray;
    }

    /**
     * Function get Stores Data
     *
     * @return void
     */
    protected function getAttributeFrontendLabel()
    {
        $labelsBlock = $this->attributeLabels;
        $labels = $labelsBlock->getLabelValues();
        $labelsArray = [];
        foreach ($labels as $key => $value) {
            $labelsArray[] = [
                'store_id' => $key,
                'value' => $value
            ];
        }
        return $labelsArray;
    }

    /**
     * Function to get Attribute Types
     *
     * @return array
     */
    public function getAttributeTypes()
    {
        return [
            [
                'value' => 'text',
                'label' => __('Text Field')
            ],
            [
                'value' => 'textarea',
                'label' => __('Text Area')
            ],
            [
                'value' => 'texteditor',
                'label' => __('Text Editor')
            ],
            [
                'value' => 'pagebuilder',
                'label' => __('Page Builder')
            ],
            [
                'value' => 'date',
                'label' => __('Date and Time')
            ],
            [
                'value' => 'datetime',
                'label' => __('Text Field')
            ],
            [
                'value' => 'boolean',
                'label' => __('Yes/No')
            ],
            [
                'value' => 'multiselect',
                'label' => __('Multiple Select')
            ],
            [
                'value' => 'select',
                'label' => __('Dropdown')
            ],
            [
                'value' => 'image',
                'label' => __('Media Image')
            ],
            [
                'value' => 'file',
                'label' => __('File')
            ],
        ];
    }
}
