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

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * B2B quote attachment resource model
 */
class QuoteAttachment extends AbstractDb
{
    /**#@+
     * B2B quote attachment table
     */
    const B2B_QUOTE_ATTACHMENT_TABLE = 'wk_b2b_quote_attachment';
    /**#@-*/

    /**
     * Define main table
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(self::B2B_QUOTE_ATTACHMENT_TABLE, 'attachment_id');
    }
}
