<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Plugin\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AttributeSet as ModifierAttributeSet;

class AttributeSet
{
    /**
     * @var \Magento\Catalog\Model\Locator\LocatorInterface
     */
    protected $locator;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    private $helperData;

    /**
     * Constructor
     *
     * @param \Magento\Catalog\Model\Locator\LocatorInterface $locator
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data     $helperData
     */
    public function __construct(
        \Magento\Catalog\Model\Locator\LocatorInterface $locator,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $helperData
    ) {
        $this->locator = $locator;
        $this->helperData = $helperData;
    }

    /**
     * Aftre Modify Meta
     *
     * @param ModifierAttributeSet $subject
     * @param mixed $result
     */
    public function afterModifyMeta(ModifierAttributeSet $subject, $result)
    {
        $typeId = $this->locator->getProduct()->getTypeId();
        if ($typeId == "booking" || $typeId == "hotelbooking") {
            $attributeSetIds = $this->helperData->getAllowedAttrSetIDsArray();
            $result['product-details']
                ['children']
                    ['attribute_set_id']
                        ['arguments']
                            ['data']
                                ['config']
                                    ['label'] = __('Booking Type');
                                    
            $result['product-details']
                ['children']
                    ['attribute_set_id']
                        ['arguments']
                            ['data']
                                ['config']
                                    ['options'] = $attributeSetIds;

        }
        return $result;
    }
}
