<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Model;

use Webkul\Mpquotesystem\Api\Data\QuoteConfigInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class QuoteConfig extends AbstractModel implements QuoteConfigInterface, IdentityInterface
{
   
    public const CACHE_TAG = 'marketplace_quote_config';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_quote_config';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_quote_config';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Mpquotesystem\Model\ResourceModel\QuoteConfig::class);
    }
    
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    
    /**
     * Set ID
     *
     * @param int $id
     *
     * @return \Webkul\Mpquotesystem\Model\Quotes
     */
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Get categeory IDs
     *
     * @return string|null
     */
    public function getCategories()
    {
        return $this->getData(self::CATEGORIES);
    }

    /**
     * Set categories
     *
     * @param string $categoryIds
     *
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setCategories($categoryIds)
    {
        return $this->setData(self::CATEGORIES, $categoryIds);
    }
    
    /**
     * Get seller id
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Set categories
     *
     * @param string $sellerId
     *
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Get min qty
     *
     * @return int|null
     */
    public function getMinQty()
    {
        return $this->getData(self::MIN_QTY);
    }

    /**
     * Set min qty
     *
     * @param int $minQty
     *
     * @return \Webkul\Mpquotesystem\Api\Data\QuoteInterface
     */
    public function setMinQty($minQty)
    {
        return $this->setData(self::MIN_QTY, $minQty);
    }
}
