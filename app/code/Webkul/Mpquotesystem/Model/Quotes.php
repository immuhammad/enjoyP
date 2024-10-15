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

use Webkul\Mpquotesystem\Api\Data\QuoteInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Quotes extends AbstractModel implements QuoteInterface, IdentityInterface
{
   
    public const CACHE_TAG = 'mpquotesystem_quotes';
    public const STATUS_UNAPPROVED = 1;
    public const STATUS_APPROVED = 2;
    public const STATUS_DECLINE = 3;
    public const STATUS_SOLD = 4;

    /**
     * @var string
     */
    protected $_cacheTag = 'mpquotesystem_quotes';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'mpquotesystem_quotes';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(\Webkul\Mpquotesystem\Model\ResourceModel\Quotes::class);
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
}
