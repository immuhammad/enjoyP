<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Model;

use Magento\Framework\View\Element\Html\Link\Current;

class Link extends \Magento\Framework\View\Element\Html\Link\Current
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $helper;

    /**
     * @param \Webkul\MpRmaSystem\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpRmaSystem\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }
    /**
     * Render block HTML.
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->$helper->isSeller()) {
            return '';
        }

        return parent::_toHtml();
    }
}
