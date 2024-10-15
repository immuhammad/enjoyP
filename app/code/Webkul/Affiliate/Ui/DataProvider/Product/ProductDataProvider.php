<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Ui\DataProvider\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * Class ProductListDataProvider
 */
class ProductDataProvider extends \Magento\Catalog\Ui\DataProvider\Product\ProductDataProvider
{
    const PRODUCT_STATUS = 'product_status';
    /**
     * Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Construct
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ProductCollection $productCollection
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $registry
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $productStatus,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\Registry $registry,
        array $addFieldStrategies = [],
        array $addFilterStrategies = [],
        array $meta = [],
        array $data = []
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $productCollectionFactory,
            $addFieldStrategies,
            $addFilterStrategies,
            $meta,
            $data
        );
        $collectionData = $this->productCollectionFactory->create();
        $collectionData->addAttributeToFilter('status', ['in' => $this->productStatus->getVisibleStatusIds()]);
        $collectionData->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $this->collection = $collectionData;
        $this->addFieldStrategies = $addFieldStrategies;
        $this->addFilterStrategies = $addFilterStrategies;
    }
}
