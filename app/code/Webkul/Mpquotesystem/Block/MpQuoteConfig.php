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

namespace Webkul\Mpquotesystem\Block;

use Magento\Catalog\Model\Category;
use Webkul\Marketplace\Helper\Data as MpDataHelper;

class MpQuoteConfig extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param \Magento\Catalog\Block\Product\Context   $context
     * @param Category                                 $category
     * @param MpDataHelper                             $mpDataHelper
     * @param \Webkul\Marketplace\Block\Product\Create $mpProductCreate
     * @param \Webkul\Mpquotesystem\Helper\Data        $helper
     * @param \Magento\Framework\App\RequestInterface  $request
     * @param array                                    $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Category $category,
        MpDataHelper $mpDataHelper,
        \Webkul\Marketplace\Block\Product\Create $mpProductCreate,
        \Webkul\Mpquotesystem\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        array $data = []
    ) {
        $this->category = $category;
        $this->mpDataHelper = $mpDataHelper;
        $this->mpProductCreate = $mpProductCreate;
        $this->helper = $helper;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     */
    public function getCategoriesTree($filter = null)
    {
        return $this->mpProductCreate->getCategoriesTree();
    }

    /**
     * GetCategoryObj
     *
     * @return array
     */
    public function getCategories()
    {
        $allowedCats = $this->mpDataHelper->getAllowedCategoryIds();
        if ($allowedCats) {
            $categories = explode(',', trim($allowedCats));
        } else {
            $categories = [];
        }
        return $categories;
    }

    /**
     * IsChildCategory
     *
     * @param Category $category
     * @return boolean
     */
    public function isChildCategory($category)
    {
        $childCats = $this->category->getAllChildren($category);
        return count($childCats)-1 > 0 ? true : false;
    }

    /**
     * GetCategoryObj
     *
     * @param int $catId
     * @return array
     */
    public function getCategory($catId)
    {
        return $this->category->load($catId);
    }

    /**
     * Get Config Data
     *
     * @return object
     */
    public function getConfigData()
    {
        $sellerId = $this->helper->getCustomerId();
        $collection = $this->helper->getQuoteconfig()
            ->getCollection()
            ->addFieldToFilter('seller_id', $sellerId)
            ->setPageSize(1)->getFirstItem();
        return $collection;
    }

    /**
     * GetHelperObject
     *
     * @return object
     */
    public function getHelperObject()
    {
        return $this->helper;
    }

    /**
     * IsRequestSecure
     *
     * @return boolean
     */
    public function isRequestSecure()
    {
        return $this->request->getRequest()->isSecure();
    }
}
