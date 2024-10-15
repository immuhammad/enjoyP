<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Mpquotesystem\Block\Adminhtml\Productgrid;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class Products extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Webkul\Accordionfaq\Model\ImagesFactory
     */
    protected $_suppliers;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * Construct
     *
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param \Magento\Framework\Registry               $coreRegistry
     * @param \Magento\Catalog\Model\ProductFactory     $_productloader
     * @param \Magento\Framework\UrlInterface           $urlBuilder
     * @param \Magento\Backend\Block\Widget\Context     $widgetContext
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Backend\Block\Widget\Context $widgetContext,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_productloader = $_productloader;
        $this->urlBuilder = $urlBuilder;
        $this->buttonList = $widgetContext->getButtonList();
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
        $this->setId('wk_quotesystem_products');
        $this->setDefaultSort('id');
        $this->getChildHtml();
    }

    /**
     * Prepare collection
     */
    protected function _prepareCollection()
    {
        $collection = $this->_productloader->create()->getCollection();
        $collection->addAttributeToSelect('*')
                    ->addAttributeToFilter('quote_status', '1')
                    ->addAttributeToFilter('type_id', ['neq'=>'bundle'])
                    ->addAttributeToFilter('visibility', ['in'=>[4]]);
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare Columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'in_products',
            [
                'header' => __('Select'),
                'type' => 'checkbox',
                'name' => 'in_products',
                'values' => $this->_getSelectedProducts(),
                'index' => 'entity_id',
                'sortable' => false,
                'filter' => false,
                'massaction' => false
                // 'header_css_class' => 'col-select',
                // 'column_css_class' => 'col-select'
            ]
        );
        $this->addColumn(
            'entity_id',
            [
                'header'    => __('Product Id'),
                'align'     => 'left',
                'width'     => '50',
                'index'     => 'entity_id',
            ]
        );
        $this->addColumn(
            'name',
            [
                'header'    => __('Name'),
                'align'     =>'left',
                'index'     => 'name',
            ]
        );
        $this->addColumn("sku", [
                "header"    => __("SKU"),
                "width"     => "80",
                "index"     => "sku"
        ]);
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'column_css_class' => 'price',
                'type' => 'currency',
                'index' => 'price',
            ]
        );
        $this->addColumn("action", [
                "header"    =>  __("Action"),
                "width"     => "100",
                "type"      => "action",
                "getter"    => "getEntityId",
                'column_css_class' => 'wk_quotesystem_column_action',
                "actions"   => [
                    [
                        "caption"   => __("Add Quote"),
                        "url"       => ["base"=> "*/*/*"],
                        "field"     => "id"
                    ]
                ],
                "filter"    => false,
                "sortable"  => false
            ]);

        return parent::_prepareColumns();
    }

    /**
     * Get selected products
     *
     * @return mixed
     */
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('products', []);

        return $products;
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction()
    {
        return $this->_authorization->isAllowed();
    }

    /**
     * Get Grid URL
     */
    public function getGridUrl()
    {
        return $this->getUrl(
            '*/*/form',
            ['_current' => true]
        );
    }

    /**
     * Prepare mass action
     *
     * @return void
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->setMassactionIdFieldOnlyIndexValue(true);

        $this->getMassactionBlock()->setFormFieldName('add_quote')->addItem(
            'add',
            [
                'label' => __('Add Multiple Quote'),
                'url' => ["base"=> "*/*/*"],
                // 'confirm' => __('Are you sure?')
            ]
        );

        return $this;
    }

    /**
     * Prepare mass actioncolumn
     */
    protected function _prepareMassactionColumn()
    {
        /** needs for correct work of mass action select functionality */
        $this->setMassactionIdField('entity_id');

        return $this;
    }
}
