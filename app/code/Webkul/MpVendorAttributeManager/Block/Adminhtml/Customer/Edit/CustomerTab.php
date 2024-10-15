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
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\UrlInterface;

class CustomerTab extends Generic implements TabInterface
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry;

    /**
     * @var \Magento\Eav\Model\Entity
     */
    protected $eavEntity;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory
     */
    protected $attributeCollection;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory
     */
    protected $vendorAttributeFactory;

     /**
      * @var \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory
      */
    protected $vendorGroupFactory;

    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory
     */
    protected $vendorAssignGroupFactory;

    /**
     * @var boolean
     */
    protected $attributesShown = false;

    /**
     * Constructor
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Eav\Model\Entity $eavEntity
     * @param \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttributeFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
     * @param \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Eav\Model\Entity $eavEntity,
        \Magento\Customer\Model\ResourceModel\Attribute\CollectionFactory $attributeCollection,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        \Webkul\MpVendorAttributeManager\Model\VendorAttributeFactory $vendorAttributeFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory,
        \Webkul\MpVendorAttributeManager\Model\VendorAssignGroupFactory $vendorAssignGroupFactory,
        array $data = []
    ) {
        $this->coreRegistry = $registry;
        $this->eavEntity = $eavEntity;
        $this->attributeCollection = $attributeCollection;
        $this->customerFactory = $customerFactory;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->helper = $helper;
        $this->vendorAttributeFactory = $vendorAttributeFactory;
        $this->vendorGroupFactory = $vendorGroupFactory;
        $this->vendorAssignGroupFactory = $vendorAssignGroupFactory;
        $this->storeManager = $context->getStoreManager();
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * Return the customer Id.
     *
     * @return int|null
     */
    public function getCustomerId()
    {
        return $this->coreRegistry->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Additional Information');
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Additional Information');
    }

    /**
     * Can show tab in tabs
     *
     * @return Bool
     */
    public function canShowTab()
    {
        if ($this->getCustomerId()) {
            return true;
        }
        return false;
    }

    /**
     * Tab is hidden
     *
     * @return Bool
     */
    public function isHidden()
    {
        if ($this->getCustomerId()) {
            return false;
        }
        return true;
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Function _toHtml
     *
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();
            return parent::_toHtml();
        } else {
            return '';
        }
    }

    /**
     * Prepare form Html.
     *
     * @return string
     */
    public function getFormHtml()
    {
        $html = parent::getFormHtml();
        $html .= $this->getLayout()->createBlock(
            \Webkul\MpVendorAttributeManager\Block\Adminhtml\Customer\Edit\CustomerTab\CustomerInformation::class
        )->toHtml();
        return $html;
    }

    /**
     * Initialize the form.
     *
     * @return $this
     */
    public function initForm()
    {
        $customerAttributes = $this->getCustomerAttribtues();
        if (!$this->canShowTab()) {
            return $this;
        }

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('customerattribute_');

        $customerId = $this->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $isGroupEnabled = $this->helper->getConfigData("group_display");
        if ($isGroupEnabled) {
            if ($customer->getIsVendorGroup()) {
                $customerAttributes = $this->getGroupAttributes($customer->getIsVendorGroup());
                $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Additional Information')]);
                if ($customerAttributes->getSize()) {
                    $this->attributesShown = true;
                    $fieldset = $this->addFieldsToFieldset($fieldset, $customerAttributes);
                }
            } else {
                $attributeGroups = $this->getAttributeGroup();
                $customerAttributes = $this->getUnassignedAttributes();

                if ($customerAttributes->getSize()) {
                    $this->attributesShown = true;
                    $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Additional Information')]);
                    $fieldset = $this->addFieldsToFieldset($fieldset, $customerAttributes);
                }
                foreach ($attributeGroups as $attributeGroup) {
                    $groupAttributes = $this->getGroupAttributes($attributeGroup->getId());
                    if ($groupAttributes->getSize()) {
                        $this->attributesShown = true;
                        $fieldset = $form->addFieldset($attributeGroup->getGroupName(), [
                            'legend' => $attributeGroup->getGroupName()
                        ]);
                        $fieldset = $this->addFieldsToFieldset($fieldset, $groupAttributes);
                    }
                }
            }
        } else {
            $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Additional Information')]);
            if ($customerAttributes && $customerAttributes->getSize()) {
                $this->attributesShown = true;
                $fieldset = $this->addFieldsToFieldset($fieldset, $customerAttributes);
            }
        }

        $this->setForm($form);
        $form->setUseContainer(true);
        return $this;
    }

    /**
     * Add Attribute Fields to Fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Magento\Customer\Model\ResourceModel\Attribute\Collection $attributes
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    public function addFieldsToFieldset($fieldset, $attributes)
    {
        $customerId = $this->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $customerData = $customer->toArray();
        foreach ($attributes as $attribute) {
            switch ($attribute->getFrontendInput()) {
                case "textarea":
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        $this->isWysiwygEnabled($attribute),
                        [
                            'name' => 'customer['.$attribute->getAttributeCode().']',
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'class' => str_replace("required", "", $attribute->getFrontendClass() ?? ''),
                            'value' => $customer->getData($attribute->getAttributeCode()),
                            'required' => $this->isAttributeRequired($attribute->getId()),
                            'config' => $this->getWysiwygConfig($attribute)
                        ]
                    );
                    break;
                case "date":
                    $value = $customer->getData($attribute->getAttributeCode());
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        'date',
                        [
                            'name' => 'customer['.$attribute->getAttributeCode().']',
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'value' => $value ? $this->formatDate($value, \IntlDateFormatter::SHORT, false) : '',
                            'required' => $this->isAttributeRequired($attribute->getId()),
                            'class' => 'custom_date_field',
                            'input_format' => DateTime::DATE_INTERNAL_FORMAT,
                            'date_format' => 'yyyy-MM-dd'
                        ]
                    );
                    break;
                case "boolean":
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        'checkbox',
                        [
                            'name' => 'customer['.$attribute->getAttributeCode().']',
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'value' => $customer->getData($attribute->getAttributeCode()),
                            'required' => $this->isAttributeRequired($attribute->getId()),
                            'onclick' => '',
                            'onchange' => 'this.value = this.checked?1:0',
                            'checked' => $customer->getData($attribute->getAttributeCode()) ? true : false
                        ]
                    );
                    break;
                case "select":
                case "multiselect":
                    $optionData = $attribute->getSource()->getAllOptions();
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        $attribute->getFrontendInput(),
                        [
                            'name' => 'customer['.$attribute->getAttributeCode().']',
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'values' => $optionData,
                            'value' => $customer->getData($attribute->getAttributeCode()),
                            'required' => $this->isAttributeRequired($attribute->getId())
                        ]
                    );
                    break;
                case "image":
                    $value = $customer->getData($attribute->getAttributeCode());
                    $allowedImageExtensions = $this->helper->getConfigData('allowede_image_extension');
                    $isRequired = $this->isAttributeRequired($attribute->getId());
                    $requiredView = $isRequired ? "none" : "";
                    $url = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ).'vendorfiles/image/'.$value;
                    $fieldId = "customfields_image_".$attribute->getAttributeCode();
                    $fieldIdString = "'$fieldId'";
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        'file',
                        [
                            'name' => $attribute->getAttributeCode(),
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'value' => $value ? 'vendorfiles'.$value :'',
                            'required' => $this->isAttributeRequired($attribute->getId()),
                            'data' => $allowedImageExtensions,
                            'note' => __('Allowed file types:').' '.$allowedImageExtensions,
                            'after_element_html' => ($value != '' && $value != 1) ?
                                '<script>
                                    if (typeof imageCode === "undefined") {
                                        var imageCode = [];
                                    }

                                    imageCode.push("'.$attribute->getAttributeCode().'");

                                    </script><a href='.$url.'
                                    onclick="imagePreview('.$fieldIdString.'); return false;">
                            <img src='.$url.'
                                    id="'.$fieldId.'"
                                    title="Preview Image"
                                    height="22" width="22" class="small-image-preview v-middle">
                                </a>
                                <div style="float:right;margin-right:20%;display:'.$requiredView.';">
                                    <input data="'.$attribute->getAttributeCode().'"
                                    data-form-part ="'.$this->getData('target_form').'"
                                        class="wkrm"
                                        type="checkbox"
                                        name="'.$attribute->getAttributeCode().'[delete]"/>'.__('Delete').'
                                    </div>
                                    <span class="data-extension" data="'.$allowedImageExtensions.'"></span>':
                                    '<span class="data-extension" data="'.$allowedImageExtensions.'"></span>'

                        ]
                    );
                    $this->addHiddenField($fieldset, $attribute, $value);
                    break;
                case "file":
                    $value = $customer->getData($attribute->getAttributeCode());
                    $allowedFileExtensions = $this->helper->getConfigData('allowede_file_extension');
                    $isRequired = $this->isAttributeRequired($attribute->getId());
                    $requiredView = $isRequired ? "none" : "";
                    $url = $this->storeManager->getStore()->getBaseUrl(
                        UrlInterface::URL_TYPE_MEDIA
                    ).'vendorfiles/file/'.$value;
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        'file',
                        [
                            'name' => $attribute->getAttributeCode(),
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'required' => $this->isAttributeRequired($attribute->getId()),
                            'data' => $allowedFileExtensions,
                            'note' => __('Allowed file types:').' '.$allowedFileExtensions,
                            'after_element_html' => ($value != '' && $value != '1') ? '<script>

                                if (typeof fileCode === "undefined") {
                                    var fileCode = [];
                                }

                                fileCode.push("'.$attribute->getAttributeCode().'");

                                </script><a target="_blank"
                                href="'.$url.'">'.__('Download').'</a>
                                <div style="float:right;margin-right:20%;display:'.$requiredView.';">
                                    <input data="'.$attribute->getAttributeCode().'"
                                    data-form-part ="'.$this->getData('target_form').'"
                                        class="wkrm"
                                        type="checkbox"
                                        name="'.$attribute->getAttributeCode().'[delete]"/>'.__('Delete').'
                                    </div>
                                    <span class="data-extension" data="'.$allowedFileExtensions.'"></span>':
                                    '<span class="data-extension" data="'.$allowedFileExtensions.'"></span>',
                        ]
                    );
                    $this->addHiddenField($fieldset, $attribute, $value);
                    break;
                default:
                    $fieldset->addField(
                        $attribute->getAttributeCode(),
                        'text',
                        [
                            'name' => 'customer['.$attribute->getAttributeCode().']',
                            'data-form-part' => $this->getData('target_form'),
                            'label' => $attribute->getFrontendLabel(),
                            'title' => $attribute->getFrontendLabel(),
                            'class' => str_replace("required", "", $attribute->getFrontendClass() ?? ''),
                            'value' => $customer->getData($attribute->getAttributeCode()),
                            'required' => $this->isAttributeRequired($attribute->getId())
                        ]
                    );
            }
        }

        return $fieldset;
    }

    /**
     * Get Customer Attributes from Vendor Attributes Collection.
     *
     * @return Collection
     */
    public function getCustomerAttribtues()
    {
        $attributeUsedForCustomer = [0,1];
        $customerAttributes = $this->vendorAttributeFactory->create()->getCollection()
                                   ->addFieldToFilter("attribute_used_for", ["in" => $attributeUsedForCustomer])
                                   ->addFieldToFilter("wk_attribute_status", "1");

        $attributeIds = $customerAttributes->getColumnValues('attribute_id');

        $typeId = $this->eavEntity->setType('customer')->getTypeId();
        $customerAttributes = $this->attributeCollection->create()
                                    ->setEntityTypeFilter($typeId)
                                    ->addFilterToMap("attribute_id", "main_table.attribute_id")
                                    ->addFieldToFilter("attribute_id", ["in" => $attributeIds])
                                    ->setOrder('sort_order', 'ASC');
        return $customerAttributes;
    }

    /**
     * Get Active Vendor Groups Collection
     *
     * @return Collection $attributeGroups
     */
    public function getAttributeGroup()
    {
        $attributeGroups = $this->vendorGroupFactory->create()->getCollection()
                                ->addFieldToFilter("status", 1);

        if ($attributeGroups->getSize()) {
            return $attributeGroups;
        }

        return [];
    }

    /**
     * Get Attributes Collecttion by Group Id
     *
     * @param int $groupId
     * @return Collection $groupAttributes
     */
    public function getGroupAttributes($groupId)
    {
        $assignedAttributes = $this->vendorAssignGroupFactory->create()->getCollection()
                                   ->addFieldToFilter("group_id", $groupId)
                                   ->getColumnValues("attribute_id");

        $groupAttributes = $this->getCustomerAttribtues()
                                ->addFieldToFilter("attribute_id", ['in' => $assignedAttributes]);

        return $groupAttributes;
    }

    /**
     * Get Unassigned Attributes Collecttion
     *
     * @return Collection $unasssignedAttributes
     */
    public function getUnassignedAttributes()
    {
        $assignedAttributes = $this->vendorAssignGroupFactory->create()->getCollection()
                                   ->getColumnValues("attribute_id");
        
        $unasssignedAttributes = $this->getCustomerAttribtues();
        if (!empty($assignedAttributes)) {
            $unasssignedAttributes->addFieldToFilter("attribute_id", ['nin' => $assignedAttributes]);
        }

        return $unasssignedAttributes;
    }

    /**
     * Check if attribute is required or not
     *
     * @param Int $attributeId
     *
     * @return Bool true|false
     */
    public function isAttributeRequired($attributeId)
    {
        $customAttribute = $this->vendorAttributeFactory->create()->load($attributeId, "attribute_id");
        if ($customAttribute) {
            return $customAttribute->getRequiredField();
        }
        return false;
    }

    /**
     * Check if WYSIWYG Editor is enabled for Attribute
     *
     * @param Object $attribute
     *
     * @return String $type
     */
    public function isWysiwygEnabled($attribute)
    {
        $frontendClass = $attribute->getFrontendClass() ?? '';
        $classes = explode(" ", $frontendClass);
        $type = "textarea";

        if (in_array('wysiwyg_enabled', $classes)) {
            $type = "editor";
        }

        return $type;
    }

    /**
     * Get WYSIWYG Editor for Attribute
     *
     * @param Object $attribute
     *
     * @return Object $config
     */
    public function getWysiwygConfig($attribute)
    {
        $type = $this->isWysiwygEnabled($attribute);
        $config = '';

        if ($type == "editor") {
            $data = [
                'add_variables' => 0,
                'add_widgets' => 0
            ];
            $config = $this->wysiwygConfig->getConfig($data);
        }

        return $config;
    }

    /**
     * Function _afterToHtml
     *
     * @param string $html
     * @return void
     */
    public function _afterToHtml($html)
    {
        if (!$this->attributesShown) {
            $html = $html."<p>".__("Sorry! No attributes are assigned to Customer.")."</p>";
        }
        return $html.'<script>
            //<![CDATA[
                require(["jquery"], function ($) {
                    $(document).ready(function () {
                        if (typeof imageCode !== "undefined") {
                            imageCode.forEach(function (val) {
                                $("#customerattribute_"+val).removeClass("required-entry _required");
                            });
                        }

                        if (typeof fileCode !== "undefined") {
                            fileCode.forEach(function (val) {
                                $("#customerattribute_"+val).removeClass("required-entry _required");
                            });
                        }

                    });
                });
            //]]>
        </script>';
    }

    /**
     * Add hidden fields for Image and File Attribute in Fieldset
     *
     * @param \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param string $value
     *
     * @return \Magento\Framework\Data\Form\Element\Fieldset $fieldset
     */
    public function addHiddenField($fieldset, $attribute, $value)
    {
        if ($value) {
            $fieldset->addField(
                $attribute->getAttributeCode()."_hidden",
                'hidden',
                [
                    'name' => 'customer['.$attribute->getAttributeCode().']',
                    'data-form-part' => $this->getData('target_form'),
                    'label' => $attribute->getFrontendLabel(),
                    'title' => $attribute->getFrontendLabel(),
                    'value' => $value,
                ]
            );
        }
        return $fieldset;
    }
}
