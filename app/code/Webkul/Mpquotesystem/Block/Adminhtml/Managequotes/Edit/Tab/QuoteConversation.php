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

namespace Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\Mpquotesystem\Model\ResourceModel\Quoteconversation\Collection;
use Webkul\Mpquotesystem\Helper\Data;
use \Webkul\Mpquotesystem\Model\QuoteconversationFactory;

class QuoteConversation extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Mpquotesystem\Model\QuoteconversationFactory
     */
    protected $_conversationFactory;

    /**
     * @var Webkul\Mpquotesystem\Model\ResourceModel\Quoteconversation\Collection
     */
    protected $_quoteconversationCollection;

    /**
     * @var Webkul\Mpquotesystem\Helper\Data
     */
    protected $_mpquoteHelper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data            $backendHelper
     * @param QuoteconversationFactory                $conversationFactory
     * @param \Magento\Framework\Registry             $coreRegistry
     * @param Collection                              $quoteconversationCollection
     * @param Data                                    $mpquoteHelper
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        QuoteconversationFactory $conversationFactory,
        \Magento\Framework\Registry $coreRegistry,
        Collection $quoteconversationCollection,
        Data $mpquoteHelper,
        array $data = []
    ) {
        $this->_conversationFactory = $conversationFactory;
        $this->_coreRegistry = $coreRegistry;
        $this->_quoteconversationCollection = $quoteconversationCollection;
        $this->_mpquoteHelper = $mpquoteHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct function to set ID
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('quoteConversation_grid');
        $this->setDefaultSort('entity_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare columns
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $params = $this->getRequest()->getParams();
        $collection = $this->_conversationFactory->create()->getCollection()
            ->addFieldToFilter('quote_id', ['eq' => $params['entity_id']])
            ->setOrder('entity_id', 'DESC');

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare column
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        
        $this->addColumn(
            'sender',
            [
                'header'    => __('Sender'),
                'sortable'  => false,
                'index'     => 'sender',
                'filter'    =>  false,
                'renderer'  => \Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Grid\RendererSenderName::class,
            ]
        );
        $this->addColumn(
            'receiver',
            [
                'header'    => __('Receiver'),
                'sortable'  => false,
                'index'     => 'receiver',
                'filter'    =>  false,
                'renderer'  => \Webkul\Mpquotesystem\Block\Adminhtml\Managequotes\Grid\RendererReceiverName::class,
            ]
        );
        $this->addColumn(
            'conversation',
            [
                'header'    => __('Conversation'),
                'sortable'  => true,
                'index'     => 'conversation',
                'type'      => 'text',
            ]
        );
        $this->addColumn(
            'created_at',
            [
                'header'    => __('Created At'),
                'sortable'  => true,
                'index'     => 'created_at',
                'type'      => 'datetime',
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get row url
     *
     * @param array $row
     *
     * @return void
     */
    public function getRowUrl($row)
    {
        return 'javascript:void(0)';
    }
}
