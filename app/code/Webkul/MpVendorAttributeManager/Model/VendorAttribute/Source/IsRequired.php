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
namespace Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class IsActive
 */
class IsRequired implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    protected $vendorAttributeBlock;

    /**
     * Constructor
     *
     * @param Webkul\MpVendorAttributeManager\Model\VendorAttribute $vendorAttributeBlock
     */
    public function __construct(\Webkul\MpVendorAttributeManager\Model\VendorAttribute $vendorAttributeBlock)
    {
        $this->vendorAttributeBlock = $vendorAttributeBlock;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->vendorAttributeBlock->getIsRequiredStatus();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
