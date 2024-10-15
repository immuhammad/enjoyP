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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Conversation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul B2BMarketplace ResourceModel Conversation collection
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
            'Webkul\B2BMarketplace\Model\Conversation',
            'Webkul\B2BMarketplace\Model\ResourceModel\Conversation'
        );
        $this->_map['fields']['entiry_id'] = 'main_table.entity_id';
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

    /**
     * getAllCustomerQuote
     *
     * @return $this
     */
    public function getAllQuoteCoversations()
    {
        $customerEntity = $this->getTable('customer_entity');
        $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
        $quoteItem = $this->getTable('wk_b2b_requestforquote_quote_item');

        $this->getSelect()->join(
            $customerEntity.' as receiver_customer_entity',
            'main_table.receiver_id = receiver_customer_entity.entity_id',
            [
                'firstname AS receiver_firstname',
                'lastname AS receiver_lastname',
                'CONCAT(receiver_customer_entity.firstname, " ", receiver_customer_entity.lastname) AS receiver_name'
            ]
        );

        $this->getSelect()->join(
            $customerEntity.' as sender_customer_entity',
            'main_table.sender_id = sender_customer_entity.entity_id',
            [
                'firstname AS sender_firstname',
                'lastname AS sender_lastname',
                'CONCAT(sender_customer_entity.firstname, " ", sender_customer_entity.lastname) AS sender_name'
            ]
        );

        $this->getSelect()->join(
            $sentQuotes.' as sent_quotes',
            'main_table.seller_quote_id = sent_quotes.entity_id',
            [
                'seller_id',
                'price',
                'qty',
                'status',
                'request_id',
                'has_samples',
                'sample_unit',
                'sample_charges',
                'sample_charge_per_unit',
                'shipping_days',
                'entity_id AS supplier_quote_id',
            ]
        );

        $this->getSelect()->join(
            $quoteItem.' as quote_item',
            'main_table.item_id = quote_item.entity_id',
            [
                'name',
                'entity_id AS mp_quote_item_id',
                'price AS buyer_price',
                'qty AS buyer_qty',
            ]
        );

        $this->getSelect()->order('main_table.item_id');
        $this->getSelect()->order('main_table.created_at');

        return $this;
    }
}
