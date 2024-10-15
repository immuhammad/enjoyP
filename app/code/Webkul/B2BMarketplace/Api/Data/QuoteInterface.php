<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

interface QuoteInterface extends ExtensibleDataInterface
{
    /**
     * Constants for keys of data array.
     */
    const ENTITY_ID = 'entity_id';
    /**#@-*/

    /**
     * Get ID.
     *
     * @return int|null
     */
    public function getId();

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\B2BMarketplace\Api\Data\QuoteInterface
     */
    public function setId($id);

    /**
     * Retrieve existing extension attributes object.
     *
     * @return \Webkul\B2BMarketplace\Api\Data\QuoteExtensionInterface|null
     */
    public function getExtensionAttributes();

    /**
     * Set an extension attributes object.
     *
     * @param \Webkul\B2BMarketplace\Api\Data\QuoteExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Webkul\B2BMarketplace\Api\Data\QuoteExtensionInterface $extensionAttributes
    );
}
