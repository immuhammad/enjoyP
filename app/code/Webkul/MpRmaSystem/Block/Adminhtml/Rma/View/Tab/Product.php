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
namespace Webkul\MpRmaSystem\Block\Adminhtml\Rma\View\Tab;

class Product extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpRmaHelper;

    /**
     * Initialize Dependencies
     *
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Backend\Model\Session\Quote $sessionQuote
     * @param \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper
     * @param array $data
     * @return void
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Backend\Model\Session\Quote $sessionQuote,
        \Webkul\MpRmaSystem\Helper\Data $mpRmaHelper,
        array $data = []
    ) {
        $this->coreRegistry  = $coreRegistry;
        $this->_sessionQuote = $sessionQuote;
        $this->mpRmaHelper   = $mpRmaHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Construct
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mprmasystem_rma_product');
        $this->setUseAjax(true);
    }

    /**
     * PrepareCollection
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $rmaId = $this->getRma()->getId();
        $collection = $this->mpRmaHelper->getRmaProductDetails($rmaId);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * PrepareColumns
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product Id'),
                'sortable' => true,
                'index' => 'product_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id',
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('SKU'),
                'index' => 'sku'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'qty',
            [
                'header' => __('Qty'),
                'index' => 'qty'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'currency_code' => $this->getStore()->getCurrentCurrencyCode(),
                'rate' => $this->getStore()->getBaseCurrency()->getRate($this->getStore()->getCurrentCurrencyCode()),
                'index' => 'price',
                'renderer' => \Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\Renderer\Price::class
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * Get Store
     *
     * @return \Magento\Backend\Model\Session\Quote
     */
    public function getStore()
    {
        return $this->_sessionQuote->getStore();
    }

    /**
     * Get getGridUrl
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('*/*/productGrid', ['_current' => true]);
    }

    /**
     * Get rma
     *
     * @return \Magento\Framework\Registry
     */
    public function getRma()
    {
        return $this->coreRegistry->registry('mprmasystem_rma');
    }
}
