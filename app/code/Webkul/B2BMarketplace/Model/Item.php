<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Model;

use Webkul\B2BMarketplace\Api\Data\ItemInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Item extends AbstractModel implements ItemInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**
     * Item cache tag.
     */
    const CACHE_TAG = 'wk_b2b_requestforquote_quote_item';

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_b2b_requestforquote_quote_item';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_b2b_requestforquote_quote_item';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\B2BMarketplace\Model\ResourceModel\Item');
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteItem();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route Item.
     *
     * @return \Webkul\B2BMarketplace\Model\Item
     */
    public function noRouteItem()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\B2BMarketplace\Api\Data\ItemInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}
