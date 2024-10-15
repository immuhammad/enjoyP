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

namespace Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Grid;

use Webkul\Mpquotesystem\Helper\Data;

class RendererReceiverName extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpquoteHelper;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param Data                           $mpQuoteHelper
     * @param array                          $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        Data $mpQuoteHelper,
        array $data = []
    ) {
        $this->_mpquoteHelper = $mpQuoteHelper;
        parent::__construct($context, $data);
    }

    /**
     * Render function
     *
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $this->_actions = [];
        if ($row->getReceiver() == 0) {
            $actions[] = __('Admin');
        } else {
            $quoteId = $row->getQuoteId();
            $quote = $this->_mpquoteHelper->getWkQuoteModel()->load($quoteId);
            $receiverData = $this->_mpquoteHelper->getCustomerData($row->getReceiver());
            if ($row->getReceiver()==$quote->getCustomerId()) {
                $actions[] = $receiverData->getName()." ".__('(Customer)');
            } else {
                $actions[] = $receiverData->getName()." ".__('(Seller)');
            }
        }
        $this->addToActions($actions);
        return $this->_actionsToHtml();
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->_actions;
        }
        foreach ($actions[0] as $action) {
            $html[] = '<span>' . $action . '</span>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
