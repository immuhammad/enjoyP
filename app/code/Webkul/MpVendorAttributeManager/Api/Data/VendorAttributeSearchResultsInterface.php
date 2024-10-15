<?php
/**
 * Webkul Software.
 *
 * @category Webkul
 * @package Webkul_MpVendorAttributeManager
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface VendorAttributeSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get attachments list.
     *
     * @return \Magento\MpVendorAttributeManager\Api\Data\VendorAttributeInterface[]
     */
    public function getItems();

    /**
     * Set attachments list.
     *
     * @param \Magento\MpVendorAttributeManager\Api\Data\VendorAttributeInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
