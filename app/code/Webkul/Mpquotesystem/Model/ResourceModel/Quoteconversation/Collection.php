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

namespace Webkul\Mpquotesystem\Model\ResourceModel\Quoteconversation;

use \Webkul\Mpquotesystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\Mpquotesystem\Model\Quoteconversation::class,
            \Webkul\Mpquotesystem\Model\ResourceModel\Quoteconversation::class
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    /**
     * AddStoreFilter
     *
     * @param object  $store
     * @param boolean $withAdmin
     * @return void
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}
