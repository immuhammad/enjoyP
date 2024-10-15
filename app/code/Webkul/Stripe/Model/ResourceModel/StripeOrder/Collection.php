<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Stripe
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Stripe\Model\ResourceModel\StripeOrder;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul Stripe ResourceModel StripeOrder
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Stripe\Model\StripeOrder::class,
            \Webkul\Stripe\Model\ResourceModel\StripeOrder::class
        );
    }

    /**
     * Add filter by store
     *
     * @param int|array|\Magento\Store\Model\Store $store
     * @param bool $withAdmin
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
