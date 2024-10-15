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

namespace Webkul\Mpquotesystem\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class TypeList implements ArrayInterface
{
    /**
     * @var Magento\Catalog\Model\CategoryFactory
     */
    protected $_categoryFactory;

    /**
     * @var Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $_categoryCollectionFactory;

    /**
     * @param \Magento\Catalog\Model\CategoryFactory                          $categoryFactory
     * @param \Webkul\Mpquotesystem\Helper\Data                               $helperData
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     */
    public function __construct(
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Webkul\Mpquotesystem\Helper\Data $helperData,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        $this->_categoryFactory = $categoryFactory;
        $this->_helperData = $helperData;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    /**
     * Get all category
     *
     * @param boolean $isActive
     * @param boolean $level
     * @param boolean $sortBy
     * @param boolean $pageSize
     * @return void
     */
    public function getCategoryCollection($isActive = true, $level = false, $sortBy = false, $pageSize = false)
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*');

        // select only active categories
        if ($isActive) {
            $collection->addIsActiveFilter();
        }

        // select categories of certain level
        if ($level) {
            $collection->addLevelFilter($level);
        }

        // sort categories by some value
        if ($sortBy) {
            $collection->addOrderField($sortBy);
        }

        // select certain number of categories
        if ($pageSize) {
            $collection->setPageSize($pageSize);
        }

        return $collection;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $arr = $this->_toArray();
        $ret = [];

        foreach ($arr as $key => $value) {
            $ret[] = [
                'value' => $key,
                'label' => $value
            ];
        }

        return $ret;
    }

    /**
     * To Array function
     *
     * @return array
     */
    private function _toArray()
    {
        $catagoryList = ['png'=>'png','pdf'=>'pdf','doc'=>'doc','jpeg'=>'jpeg','docx'=>'docx'];
        return $catagoryList;
    }

    /**
     * Get parent category
     *
     * @param string $path
     * @return string
     */
    private function _getParentName($path = '')
    {
        $parentName = '';
        $rootCats = [1,2];

        $catTree = explode("/", $path);
        // Deleting category itself
        array_pop($catTree);

        if ($catTree && (count($catTree) > count($rootCats))) {
            foreach ($catTree as $catId) {
                if (!in_array($catId, $rootCats)) {
                    $category = $this->_helperData->loadData($this->_categoryFactory->create(), $catId);
                    $categoryName = $category->getName();
                    $parentName .= $categoryName . ' -> ';
                }
            }
        }

        return $parentName;
    }
}
