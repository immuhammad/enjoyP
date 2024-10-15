<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Block\Adminhtml\Group\Edit\Tab;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;
use Magento\Review\Helper\Action\Pager;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup\CollectionFactory;
use Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory as VendorAttributeCollection;

class Attributes extends Extended
{
    /**
     * @var Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var Magento\Review\Helper\Action\Pager
     */
    protected $pager;

    /**
     * @var Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAssignGroup\CollectionFactory
     */
    protected $_assignCollection;

    /**
     * @var Webkul\MpVendorAttributeManager\Model\ResourceModel\VendorAttribute\CollectionFactory
     */
    protected $_attributeCollection;

    /**
     * @var Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $coreRegistry
     * @param Pager $pager
     * @param JsonHelper $jsonHelper
     * @param CollectionFactory $assignCollection
     * @param VendorAttributeCollection $attributeCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $coreRegistry,
        Pager $pager,
        JsonHelper $jsonHelper,
        CollectionFactory $assignCollection,
        VendorAttributeCollection $attributeCollection,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->pager = $pager;
        $this->jsonHelper = $jsonHelper;
        $this->_attributeCollection = $attributeCollection;
        $this->_assignCollection = $assignCollection;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('assign_group_attribute');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * Add column filtering conditions to collection
     *
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     *
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'attribute_id') {
            $selectedIds = $this->getSelectedValues();
            if (empty($selectedIds)) {
                $selectedIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('id', ['in' => $selectedIds]);
            } else {
                if ($selectedIds) {
                    $this->getCollection()->addFieldToFilter('id', ['nin' => $selectedIds]);
                }
            }
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * Process collection after loading
     *
     * @return $this
     */
    protected function _afterLoadCollection()
    {
        $this->pager->setStorageId('group');
        
        return parent::_afterLoadCollection();
    }

    /**
     * Get prepared collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $attributeCollection = $this->_attributeCollection->create()
                                    ->getUnassignedAttributeCollection($this->getGroupId());
        
        $this->setCollection($attributeCollection);
        return parent::_prepareCollection();
    }

    /**
     * Return Attribute ids in JSON Format
     *
     * @return string
     **/
    public function getSelectedAttrJson()
    {
        $attributesArray = [];
        $attributes = array_keys($this->getSelectedGroupAttr());
        
        foreach ($attributes as $key => $value) {
            $attributesArray[$value] = 0;
        }
        
        return $this->jsonHelper->jsonEncode((object)$attributesArray);
    }

    /**
     * Return Attributes assigned to Attribute Group
     *
     * @return array|null
     */
    public function getSelectedGroupAttr()
    {
        $attributes = [];
        $groupCollection = $this->_assignCollection->create()
                                ->addFieldToFilter('group_id', $this->getGroupId());

        foreach ($groupCollection as $model) {
            $attributes[$model->getAttributeId()] = ['position' => $model->getAttributeId()];
        }
        return $attributes;
    }

    /**
     * Initialize grid columns
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'attribute_id',
            [
                'header_css_class' => 'a-center',
                'type' => 'checkbox',
                'name' => 'group',
                'values' => $this->getSelectedValues(),
                'align' => 'center',
                'index' => 'attribute_id',
                'use_index' => true
            ]
        );
        $this->addColumn(
            'attribute_code',
            [
                'header' => __('Attribute Code'),
                'sortable' => true,
                'index' => 'attribute_code',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'frontend_label',
            [
                'header' => __('Attribute Label'),
                'sortable' => true,
                'index' => 'frontend_label',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get Vendor Attribute Id's assigned to Group
     *
     * @return array
     **/
    public function getSelectedValues()
    {
        $assignedIds = [];
        if ($this->getGroupId()) {
            $assignCollection = $this->_assignCollection->create()->getAssignedAttributeIds($this->getGroupId());
            if ($assignCollection->getSize()) {
                foreach ($assignCollection as $assignedAttributes) {
                    $assignedIds[] = $assignedAttributes['attribute_id'];
                }
            }
        }

        return $assignedIds;
    }

     /**
      * Return row url for js event handlers
      *
      * @param mixed $row
      *
      * @return string
      */
    public function getRowUrl($row)
    {
        return "javascript:void(0)";
    }

    /**
     * Retrieve grid reload url
     *
     * @return string;
     */
    public function getGridUrl()
    {
        return $this->getUrl('vendorattribute/*/attrGrid', ['_current' => true]);
    }

    /**
     * Get current Vendor Attribute Group Id
     *
     * @return array|null
     */
    public function getGroupId()
    {
        if ($this->_coreRegistry->registry('group_id')) {
            return $this->_coreRegistry->registry('group_id');
        } else {
            return $this->getRequest()->getParam('id');
        }
    }
}
