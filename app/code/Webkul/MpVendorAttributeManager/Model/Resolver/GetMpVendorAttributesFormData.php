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

namespace Webkul\MpVendorAttributeManager\Model\Resolver;

use Magento\CustomerGraphQl\Model\Customer\GetCustomer;
use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\CustomerGraphQl\Model\Customer\ExtractCustomerData;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * GetMpVendorAttributesFormData field resolver, used for GraphQL request processing.
 */
class GetMpVendorAttributesFormData implements ResolverInterface
{
    /**
     * @var GetCustomer
     */
    private $getCustomer;

    /**
     * @var ExtractCustomerData
     */
    private $extractCustomerData;

    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    private $helper;

    /**
     * Constructor
     *
     * @param GetCustomer $getCustomer
     * @param ExtractCustomerData $extractCustomerData
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     */
    public function __construct(
        GetCustomer $getCustomer,
        ExtractCustomerData $extractCustomerData,
        \Webkul\MpVendorAttributeManager\Helper\Data $helper
    ) {
        $this->getCustomer = $getCustomer;
        $this->extractCustomerData = $extractCustomerData;
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
        $purpose = $args['purpose'];

        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer() && $purpose == "BECOME_SELLER") {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        if ($purpose == 'CREATE_CUSTOMER') {
            $isForSeller = 0;
        } else {
            $isForSeller = 1;
        }

        $returnArray = [];
        $returnArray['purpose'] = $purpose;
        $returnArray['attributesData'] = [];
        $returnArray['groupsData'] = [];

        if ($this->helper->getConfigData('group_display')) {
            $groups = $this->helper->getAttributeGroups();
            foreach ($groups as $group) {
                $returnArray['groupsData'][] = $this->getVendorGroupFormData($group, $isForSeller);
            }
        } else {
            $customAttributes = $this->helper->getAttributeCollection($isForSeller);
            if ($customAttributes) {
                foreach ($customAttributes as $attribute) {
                    $returnArray['attributesData'][] = $this->getVendorAttributeFormData($attribute);
                }
            }
        }
        return $returnArray;
    }

    /**
     * Function getVendorGroupFormData
     *
     * @param array $group
     * @param int $isForSeller
     * @return array
     */
    private function getVendorGroupFormData($group, $isForSeller)
    {
        $group['attributesData'] = [];
        $groupAttributes = $this->helper->getAttributesByGroupId($group['group_id']);
        foreach ($groupAttributes as $attribute) {
            if ($isForSeller && $attribute->getAttributeUsedFor() == 1) {
                continue;
            } elseif (!$isForSeller && $attribute->getAttributeUsedFor() == 2) {
                continue;
            }
            $group['attributesData'][] = $this->getVendorAttributeFormData($attribute);
        }
        return $group;
    }

    /**
     * Function getVendorAttributeFormData
     *
     * @param object $attribute
     * @return array
     */
    private function getVendorAttributeFormData($attribute)
    {
        $attributeCode = $attribute->getAttributeCode();
        $frontendInput = $attribute->getFrontendInput();
        $options = [];
        if ($frontendInput == 'select' || $frontendInput == 'multiselect') {
            $options = $attribute->getSource()->getAllOptions();
        }
        $attributeData = [
            'attribute_code' => $attributeCode,
            'frontend_input' => $frontendInput,
            'frontend_label' => $attribute->getStoreLabel(),
            'frontend_class' => $attribute->getFrontendClass() ?? '',
            'required_field' => $attribute->getRequiredField(),
            'sort_order' => $attribute->getSortOrder(),
            'show_in_front' => $attribute->getShowInFront(),
            'wk_attribute_status' => $attribute->getWkAttributeStatus(),
            'attribute_used_for' => $attribute->getAttributeUsedFor(),
            'options' => $options
        ];
        return $attributeData;
    }
}
