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

namespace Webkul\Mpquotesystem\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class Managequotes extends \Magento\Backend\App\Action
{
    /**
     * @var Webkul\Mpquotesystem\Model\QuotesFactory
     */
    protected $mpquotes;

    /**
     * @param Action\Context $context
     * @param \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
     */
    public function __construct(
        Action\Context $context,
        \Webkul\Mpquotesystem\Model\QuotesFactory $mpquotes
    ) {
        parent::__construct($context);
        $this->_mpquote = $mpquotes;
    }

    /**
     * Check for is allowed
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed(
            'Webkul_Mpquotesystem::mpquotes'
        );
    }
}
