<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Cancellation;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    
    /**
     * Define model & resource model
     */
    protected function _construct()
    {
        $this->_init(
            \Webkul\MpAdvancedBookingSystem\Model\Cancellation::class,
            \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Cancellation::class
        );
    }
}
