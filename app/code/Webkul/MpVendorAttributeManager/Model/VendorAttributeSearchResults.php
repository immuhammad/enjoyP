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
declare(strict_types=1);

namespace Webkul\MpVendorAttributeManager\Model;

use Webkul\MpVendorAttributeManager\Api\Data\VendorAttributeSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

/**
 * Service Data Object with Page search results.
 */
class VendorAttributeSearchResults extends SearchResults implements VendorAttributeSearchResultsInterface
{
}
