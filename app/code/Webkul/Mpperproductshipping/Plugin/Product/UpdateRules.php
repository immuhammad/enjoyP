<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpperproductshipping
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpperproductshipping\Plugin\Product;

class UpdateRules
{

    /**
     * @param \Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules $rulesObject
     * @param callable $proceed
     * @param \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
     * @param array $data
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundBuild(
        \Magento\Catalog\Ui\DataProvider\CatalogEavValidationRules $rulesObject,
        \Closure $proceed,
        \Magento\Catalog\Api\Data\ProductAttributeInterface $attribute,
        array $data
    ) {
        $rules = $proceed($attribute, $data);
        if ($attribute->getAttributeCode() == 'mp_shipping_charge') {
            $validationClasses = explode(' ', $attribute->getFrontendClass());
            foreach ($validationClasses as $class) {
                $rules[$class] = true;
            }
        }
        return $rules;
    }
}
