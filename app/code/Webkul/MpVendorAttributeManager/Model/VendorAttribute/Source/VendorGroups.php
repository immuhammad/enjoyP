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
 * Class VendorGroups
 *
 * Webkul\MpVendorAttributeManager\Model\VendorAttribute\Source
 */
class VendorGroups implements OptionSourceInterface
{
    /**
     * @var \Webkul\MpVendorAttributeManager\Model\VendorAttribute
     */
    protected $vendorGroupFactory;

    /**
     * Constructor
     *
     * @param Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
     */
    public function __construct(
        \Webkul\MpVendorAttributeManager\Model\VendorGroupFactory $vendorGroupFactory
    ) {
        $this->vendorGroupFactory = $vendorGroupFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableGroups = $this->vendorGroupFactory->create()
            ->getCollection()
            ->addFieldToFilter('status', ['eq' => 1]);
        $options = [
            ['value' => '', 'label' => __('Select')]
        ];
        foreach ($availableGroups as $value) {
            $options[] = [
                'label' => $value->getGroupName(),
                'value' => $value->getId(),
            ];
        }
        return $options;
    }
}
