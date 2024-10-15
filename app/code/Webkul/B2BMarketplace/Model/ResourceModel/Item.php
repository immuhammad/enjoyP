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
namespace Webkul\B2BMarketplace\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;

class Item extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('wk_b2b_requestforquote_quote_item', 'entity_id');
    }

    /**
     * Load an object using 'identifier' field if there's no field specified and value is not numeric.
     *
     * @param AbstractModel $object
     * @param mixed $value
     * @param string $field
     *
     * @return $this
     */
    public function load(AbstractModel $object, $value, $field = null)
    {
        if (!is_numeric($value) && $field == null) {
            $field = 'identifier';
        }

        return parent::load($object, $value, $field);
    }
}
