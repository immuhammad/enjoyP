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
 * GetMpVendorAttributes field resolver, used for GraphQL request processing.
 */
class GetMpVendorAttributes implements ResolverInterface
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
        /** @var ContextInterface $context */
        if (false === $context->getExtensionAttributes()->getIsCustomer()) {
            throw new GraphQlAuthorizationException(__('The current customer isn\'t authorized.'));
        }

        $purpose = $args['purpose'];
        $customerId = $context->getUserId();
        $customer = $this->helper->loadCustomer($customerId);
        if ($purpose == 'EDIT_SELLER_PROFILE') {
            $sellerStatus = $this->helper->getSellerStatusByCustomerId($customerId);
        } else {
            $sellerStatus = 0;
        }

        $returnArray = [];
        $customAttributes = $this->helper->getAttributeCollection($sellerStatus);
        if ($this->helper->getConfigData('group_display') && $customer->getIsVendorGroup()) {
            $customAttributes = $this->helper->getAttributeCollectionByGroup(
                $customer->getIsVendorGroup(),
                $sellerStatus
            );
        }

        $attributesData = [];
        if ($customAttributes
            && ($purpose == 'EDIT_CUSTOMER' || ($purpose == 'EDIT_SELLER_PROFILE' && $sellerStatus))
        ) {
            foreach ($customAttributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $frontendInput = $attribute->getFrontendInput();
                $rawValue = $customer->getData($attributeCode);
                list($value, $options) = $this->helper->getValueOptionsForAttribute($attribute, $rawValue);
                $attributesData[] = [
                    'attribute_code' => $attributeCode,
                    'frontend_input' => $frontendInput,
                    'frontend_label' => $attribute->getStoreLabel(),
                    'frontend_class' => $attribute->getFrontendClass() ?? '',
                    'required_field' => $attribute->getRequiredField(),
                    'sort_order' => $attribute->getSortOrder(),
                    'show_in_front' => $attribute->getShowInFront(),
                    'wk_attribute_status' => $attribute->getWkAttributeStatus(),
                    'attribute_used_for' => $attribute->getAttributeUsedFor(),
                    'value' => $value,
                    'options' => $options
                ];
            }
        }

        $returnArray['purpose'] = $purpose;
        $returnArray['attributesData'] = $attributesData;
        return $returnArray;
    }
}
