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
namespace Webkul\B2BMarketplace\Model\ResourceModel\Quote;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Webkul Marketplace ResourceModel Seller collection
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
            'Webkul\B2BMarketplace\Model\Quote',
            'Webkul\B2BMarketplace\Model\ResourceModel\Quote'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
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
     * Retrieve clear select
     *
     * @return \Magento\Framework\DB\Select
     */
    protected function _getClearSelect()
    {
        return $this->_buildClearSelect();
    }

    /**
     * Build clear select
     *
     * @param \Magento\Framework\DB\Select $select
     * @return \Magento\Framework\DB\Select
     */
    protected function _buildClearSelect($select = null)
    {
        if (null === $select) {
            $select = clone $this->getSelect();
        }
        $select->reset(\Magento\Framework\DB\Select::ORDER);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_COUNT);
        $select->reset(\Magento\Framework\DB\Select::LIMIT_OFFSET);
        $select->reset(\Magento\Framework\DB\Select::COLUMNS);

        return $select;
    }

    /**
     * getAllCustomerQuote
     *
     * @return $this
     */
    public function getAllCustomerQuote()
    {
        $quoteItem = $this->getTable('wk_b2b_requestforquote_quote_item');
        $this->getSelect()->join(
            $quoteItem.' as quote_item',
            'main_table.entity_id = quote_item.quote_id',
            [
                'quote_item.status AS item_status',
                'quote_item.name AS name',
                'quote_item.entity_id AS quote_item_id',
                'quote_item.buying_lead_status AS buying_lead_status',
                'quote_item.product_id AS item_product_id',
                'SUM(quote_item.qty) AS total_qty',
                'SUM(quote_item.price) AS total_price',
                'count(quote_item.product_id) AS total_products',
                'GROUP_CONCAT(quote_item.name) AS names'
            ]
        );
        $this->getSelect()->group('quote_item.quote_id');
        return $this;
    }

    /**
     * getAllSellerQuote
     *
     * @param int $sellerId
     * @param bool $status
     * @return $this
     */
    public function getAllSellerQuoteBySellerId($sellerId, $status = 0)
    {
        $quoteItem = $this->getTable('wk_b2b_requestforquote_quote_item');
        $this->getSelect()->join(
            $quoteItem.' as quote_item',
            'main_table.entity_id = quote_item.quote_id',
            [
                'quote_item.status AS item_status',
                'quote_item.name AS name',
                'quote_item.buying_lead_status AS buying_lead_status',
                'quote_item.product_id AS item_product_id',
                'SUM(quote_item.qty) AS total_qty',
                'SUM(quote_item.price) AS total_price',
                'count(quote_item.entity_id) AS total_products',
                'GROUP_CONCAT(quote_item.name) AS names'
            ]
        );

        // Query for getting records from wk_b2b_requestforquote_sent_quotes if seller sent quote for buyingleads
        if ($rowIds = implode(',', $this->getAllSentQuoteDistinctItemRowIds($sellerId, $status))) {
            $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
            $this->getSelect()->joinLeft(
                $sentQuotes.' as sent_quotes',
                'quote_item.entity_id = sent_quotes.quote_item_id and sent_quotes.entity_id IN ('.$rowIds.')',
                [
                    'sent_quotes.entity_id as sent_quotes_id',
                    'sent_quotes.quote_item_id as sent_quotes_item_id',
                    'sent_quotes.status as sent_quotes_status',
                    'sent_quotes.seller_id as sent_quotes_seller_id'
                ]
            );
            $this->getSelect()->where(
                '(quote_item.seller_id='.$sellerId.') OR (quote_item.seller_id=0 AND
                 sent_quotes.seller_id="'.$sellerId.'")'
            );
        } else {
            $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
            $this->getSelect()->joinLeft(
                $sentQuotes.' as sent_quotes',
                'quote_item.entity_id = sent_quotes.quote_item_id',
                [
                    'sent_quotes.entity_id as sent_quotes_id',
                    'sent_quotes.quote_item_id as sent_quotes_item_id',
                    'sent_quotes.status as sent_quotes_status',
                    'sent_quotes.seller_id as sent_quotes_seller_id'
                ]
            );
            $this->getSelect()->where('(quote_item.seller_id='.$sellerId.') 
            OR (quote_item.seller_id=0 AND sent_quotes.seller_id='.$sellerId.')');
        }

        $this->getSelect()->group('quote_item.quote_id');

        return $this;
    }

    /**
     * Retrieve all sent quote status row ids for unique quote_item_id.
     *
     * @param bool $status
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getAllSentQuoteDistinctItemRowIds($sellerId, $status = 0)
    {
        $sentQuotes = $this->getTable('wk_b2b_requestforquote_sent_quotes');
        if ($status) {
            $idsSelect = 'SELECT '.'q1.entity_id, q1.quote_item_id
                FROM '.$sentQuotes.' q1
                INNER JOIN (SELECT subQuery.quote_item_id, MAX(subQuery.entity_id) AS max_sent_quote_id
                        FROM '.$sentQuotes.' subQuery WHERE subQuery.seller_id = '.
                        $sellerId.' GROUP BY subQuery.quote_item_id order by created_at) q2
                ON (q1.entity_id = q2.max_sent_quote_id) WHERE q1.status = '.$status;
        } else {
            $idsSelect = 'SELECT '.'q1.entity_id, q1.quote_item_id
                FROM '.$sentQuotes.' q1
                INNER JOIN (SELECT subQuery.quote_item_id, MAX(subQuery.entity_id) AS max_sent_quote_id
                        FROM '.$sentQuotes.' subQuery WHERE subQuery.seller_id = '.
                        $sellerId.' GROUP BY subQuery.quote_item_id) q2
                ON (q1.entity_id = q2.max_sent_quote_id)';
        }

        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }

    /**
     * getBuyingLeadsTotalSuppliers
     *
     * @param int|string $limit
     * @param int|string $offset
     * @return array
     */
    public function getBuyingLeadsTotalSuppliers($limit = null, $offset = null)
    {
        $idsSelect = $this->_getClearSelect();
        $buyingLeads = $this->getTable('wk_b2b_requestforquote_buying_leads');
        $idsSelect->join(
            $buyingLeads.' as buying_leads',
            'main_table.entity_id = buying_leads.quote_id',
            [
                'count(distinct(buying_leads.seller_id)) AS total_suppliers'
            ]
        );
        $idsSelect->group('buying_leads.quote_id');
        $idsSelect->limit($limit, $offset);
        $idsSelect->resetJoinLeft();
        return $this->getConnection()->fetchCol($idsSelect, $this->_bindParams);
    }
}
