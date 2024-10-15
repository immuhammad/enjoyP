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
 
use Magento\Framework\Model\AbstractModel;
 
class Conversation extends \Magento\Framework\Model\AbstractModel
{
    const CACHE_TAG = 'wk_b2b_requestforquote_quote_conversation';

    const SENDER_TYPE_CUSTOMER = 0;

    const SENDER_TYPE_SELLER = 1;

    /**
     * @var string
     */
    protected $_cacheTag = 'wk_b2b_requestforquote_quote_conversation';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_b2b_requestforquote_quote_conversation';

    protected function _construct()
    {
        $this->_init('Webkul\B2BMarketplace\Model\ResourceModel\Conversation');
    }
}
