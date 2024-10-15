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

use Magento\Framework\GraphQl\Exception\GraphQlAuthorizationException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * GetAttributesForSellerProfile field resolver, used for GraphQL request processing.
 */
class GetAttributesForSellerProfile implements ResolverInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    private $mpHelper;

    /**
     * Constructor
     *
     * @param \Webkul\MpVendorAttributeManager\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Webkul\MpVendorAttributeManager\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
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
        $returnArray = [];
        $attributesData = [];
        $shopUrl = $args['shopUrl'];
        if ($shopUrl) {
            $data = $this->mpHelper->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                $sellerId = $seller->getSellerId();
            }
        }
        if ($this->helper->getConfigData('visible_profile') && !empty($sellerId)) {
            $customer = $this->helper->loadCustomer($sellerId);
            $vendorAttributes = $this->helper->getAttributeCollection(true, true);
            if ($this->helper->getConfigData('group_display') && $customer->getIsVendorGroup()) {
                $vendorAttributes = $this->helper->getAttributeCollectionByGroup(
                    $customer->getIsVendorGroup(),
                    $sellerStatus
                );
            }
            
            if ($vendorAttributes) {
                foreach ($vendorAttributes as $attribute) {
                    if ($attribute->getAttributeUsedFor() == 1 || $attribute->getShowInFront() == 0) {
                        continue;
                    }
                    if (($attribute->getFrontendInput() == 'image' && !$this->helper->getConfigData('image_display'))
                        || ($attribute->getFrontendInput() == 'file' && !$this->helper->getConfigData('file_display'))
                    ) {
                        continue;
                    }
                    $attributeCode = $attribute->getAttributeCode();
                    $frontendInput = $attribute->getFrontendInput();
                    $rawValue = $customer->getData($attributeCode);
                    $value = $this->helper->getAttributeValueForSellerProfile($attribute, $rawValue);
                    if ($value != '') {
                        $attributesData[] = [
                            'frontend_input' => $frontendInput,
                            'frontend_label' => $attribute->getStoreLabel(),
                            'sort_order' => $attribute->getSortOrder(),
                            'value' => $value,
                        ];
                    }
                }
            }

        }
        $returnArray['attributesData'] = $attributesData;
        return $returnArray;
    }
}
