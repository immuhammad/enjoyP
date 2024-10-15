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
 * Class GroupStatus
 *
 * Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source
 */
class GroupStatus implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    protected $vendorGroup;

    /**
     * Constructor
     *
     * @param Webkul\MpVendorAttributeManager\Model\VendorGroup $vendorGroup
     */
    public function __construct(\Webkul\MpVendorAttributeManager\Model\VendorGroup $vendorGroup)
    {
        $this->vendorGroup = $vendorGroup;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->vendorGroup->getAvailableStatuses();
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
