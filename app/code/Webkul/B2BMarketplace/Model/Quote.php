<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Model;

use Magento\Framework\Model\AbstractExtensibleModel;
use Webkul\B2BMarketplace\Api\Data\QuoteInterface;
 
class Quote extends AbstractExtensibleModel implements QuoteInterface
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Webkul\B2BMarketplace\Model\ResourceModel\Quote::class);
        parent::_construct();
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->getData(QuoteInterface::ENTITY_ID);
    }

    /**
     * @inheritdoc
     */
    public function setId($id)
    {
        return $this->setData(QuoteInterface::ENTITY_ID, $id);
    }

    /**
     * @inheritdoc
     */
    public function getExtensionAttributes()
    {
        return $this->getData(self::EXTENSION_ATTRIBUTES_KEY);
    }

    /**
     * @inheritdoc
     */
    public function setExtensionAttributes(
        \Webkul\B2BMarketplace\Api\Data\QuoteExtensionInterface $extensionAttributes
    ) {
        return $this->setData(self::EXTENSION_ATTRIBUTES_KEY, $extensionAttributes);
    }
}
