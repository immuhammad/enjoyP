<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Model\Api;

use Magento\Framework\Api\SearchResultsInterfaceFactory as SearchResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistCollectionFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Api\SortOrder;

class BookingSystem implements \Webkul\MpAdvancedBookingSystem\Api\BookingSystemInterface
{

    /**
     * @var SearchResultFactory
     */
    protected $searchResultFactory;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * @var SaleslistCollectionFactory
     */
    protected $saleslistCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $marketplaceProducts;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory
     */
    protected $infoCollection;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $catalogProducts;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $visibilityModel;

    /**
     * @var \Magento\Authorization\Model\CompositeUserContext
     */
    protected $userContext;

    /**
     * @param SearchResultFactory $searchResultFactory
     * @param ResourceConnection $resource
     * @param SaleslistCollectionFactory $saleslistCollectionFactory
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $marketplaceProducts
     * @param \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollection
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProducts
     * @param \Magento\Catalog\Model\Product\Visibility $visibilityModel
     * @param \Magento\Authorization\Model\CompositeUserContext $userContext
     */
    public function __construct(
        SearchResultFactory $searchResultFactory,
        ResourceConnection $resource,
        SaleslistCollectionFactory $saleslistCollectionFactory,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $marketplaceProducts,
        \Webkul\MpAdvancedBookingSystem\Model\ResourceModel\Info\CollectionFactory $infoCollection,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $catalogProducts,
        \Magento\Catalog\Model\Product\Visibility $visibilityModel,
        \Magento\Authorization\Model\CompositeUserContext $userContext
    ) {
        $this->searchResultFactory = $searchResultFactory;
        $this->_resource = $resource;
        $this->saleslistCollectionFactory = $saleslistCollectionFactory;
        $this->marketplaceProducts = $marketplaceProducts;
        $this->infoCollection = $infoCollection;
        $this->catalogProducts = $catalogProducts;
        $this->visibilityModel = $visibilityModel;
        $this->userContext = $userContext;
    }

    /**
     * Sales List Collection Object
     *
     * @api
     * @param int $productId
     * @param int $sellerId
     *
     * @return \Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory
     */
    public function salesListCollectionObject($productId, $sellerId)
    {
        return $this->saleslistCollectionFactory->create()
                    ->addFieldToFilter('mageproduct_id', $productId)
                    ->addFieldToFilter('seller_id', $sellerId);
    }

    /**
     * Get Bookings List
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getBookingsList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        try {
            $sellerId = $this->userContext->getUserId();

            $eavAttributeSet = $this->_resource->getTableName('eav_attribute_set');

            $marketplaceProduct = $this->marketplaceProducts->create()
                                        ->addFieldToFilter('seller_id', $sellerId);
            $allIds = $marketplaceProduct->getAllIds();

            $bookingProduct = $this->infoCollection->create();
            $allBookingIds = $bookingProduct->getAllProductIds();
            $allIds = array_unique(array_intersect($allIds, $allBookingIds));

            /** @var Collection $collection */
            $collectionData = $this->catalogProducts->create();
            $collectionData->addAttributeToSelect(
                '*'
            );
            $collectionData->addFieldToFilter('entity_id', ['in' => $allIds]);
            $collectionData->joinField(
                'qty',
                'cataloginventory_stock_item',
                'qty',
                'product_id=entity_id',
                '{{table}}.stock_id=1',
                'left'
            );
            $collectionData->setFlag('has_stock_status_filter');
            /** Join for Attribute Set Name Start*/
            $sql = $eavAttributeSet.' as eas';
            $cond = 'e.attribute_set_id = eas.attribute_set_id';
            $fields = ['attribute_set_name' => 'attribute_set_name'];
            $collectionData->getSelect()
                    ->join($sql, $cond, $fields);
            $collectionData->addFilterToMap('product_name', 'cpev.value');

            /** Add filter in collection */
            foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
                foreach ($filterGroup->getFilters() as $filter) {
                    $flag = true;
                    if ($filter->getField() == 'visibility'
                        || $filter->getField() == 'qtySold'
                        || $filter->getField() == 'qtyConfirmed'
                        || $filter->getField()== 'qtyPending'
                    ) {
                        $flag = false;
                    }
                    if ($flag) {
                        $condition = $filter->getConditionType() ?: 'eq';
                        $collectionData->addFieldToFilter($filter->getField(), [$condition => $filter->getValue()]);
                    }
                }
            }

            /** Sort order collection */
            $sortOrdersData = $searchCriteria->getSortOrders();
            if ($sortOrdersData) {
                foreach ($sortOrdersData as $sortOrder) {
                    $collectionData->addOrder(
                        $sortOrder->getField(),
                        ($sortOrder->getDirection() == SortOrder::SORT_ASC) ? 'ASC' : 'DESC'
                    );
                }
            }

            $data = [];
            /** Create array of booking product for return */
            foreach ($collectionData as $product) {
                $visibility = $this->visibilityModel->getOptionText($product->getVisibility());
                $qtySoldArr = $this->salesListCollectionObject($product->getId(), $sellerId)->getAllSoldQty();
                $qtyConfirmedArr = $this->salesListCollectionObject($product->getId(), $sellerId)
                                        ->addFieldToFilter(
                                            'cpprostatus',
                                            1
                                        )->getAllSoldQty();
                $qtyPendingArr = $this->salesListCollectionObject($product->getId(), $sellerId)
                            ->addFieldToFilter(
                                'cpprostatus',
                                0
                            )->getAllSoldQty();
                $qtySold = 0;
                $qtyConfirmed = 0;
                $qtyPending = 0;
                if (isset($qtySoldArr['0']['qty']) && $qtySoldArr['0']['qty']) {
                    $qtySold = $qtySoldArr['0']['qty'];
                }
                if (isset($qtyConfirmedArr['0']['qty']) && $qtyConfirmedArr['0']['qty']) {
                    $qtyConfirmed = $qtyConfirmedArr['0']['qty'];
                }
                if (isset($qtyPendingArr['0']['qty']) && $qtyPendingArr['0']['qty']) {
                    $qtyPending = $qtyPendingArr['0']['qty'];
                }
                $tempArr['qty_penfing'] = $qtyPending;
                $tempArr['qty_confirmed'] = $qtyConfirmed;
                $tempArr['qty_sold'] = $qtySold;
                $tempArr['id'] = $product->getid();
                $tempArr['price'] = $product->getPrice();
                $tempArr['type'] = $product->getAttributeSetName();
                $tempArr['sku'] = $product->getSku();
                $tempArr['name'] = $product->getName();
                $tempArr['visibility'] = $visibility;
                $data[] = $tempArr;
            }
            $searchResults = $this->searchResultFactory->create();
            $searchResults->setSearchCriteria($searchCriteria);
            $searchResults->setItems($data);
            $searchResults->setTotalCount($collectionData->getSize());
            return $searchResults;
        } catch (\Exception $e) {
            return [
                [
                    'error' => true ,
                    'message' => __('Something went wrong')
                ]
            ];
        }
    }
}
