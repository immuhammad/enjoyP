<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Product;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\Layer;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Helper\Data as HelperData;

class Search extends Action
{
    /**
     * Catalog layer
     *
     * @var \Magento\Catalog\Model\Layer
     */
    protected $_catalogLayer;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_product;

    /**
     * @var ProductCollectionFactory
     */
    protected $catalogProductCollection;

    /**
     * @var CollectionFactory
     */
    protected $productCollection;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_registry = null;

    /**
     * @param Context $context
     * @param Resolver $layerResolver
     * @param ProductFactory $product
     * @param ProductCollectionFactory $catalogProductCollection
     * @param CollectionFactory $productCollection
     * @param HelperData $helperData
     */
    public function __construct(
        Context $context,
        Resolver $layerResolver,
        ProductFactory $product,
        ProductCollectionFactory $catalogProductCollection,
        CollectionFactory $productCollection,
        HelperData $helperData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->_catalogLayer = $layerResolver->get();
        $this->_product = $product;
        $this->catalogProductCollection = $catalogProductCollection;
        $this->productCollection = $productCollection;
        $this->helperData = $helperData;
        $this->resultPageFactory = $resultPageFactory;
        $this->_registry = $registry;
    }

    /**
     * Get catalog layer model
     *
     * @return \Magento\Catalog\Model\Layer
     */
    public function getLayer()
    {
        return $this->_catalogLayer;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $requestedData = $this->getRequest()->getParams();
            $responseData = [];
            $responseData['size'] = 0;
            $responseData['products'] = [];
            if (isset($requestedData['q']) && !empty($requestedData['q']) && strlen($requestedData['q']) > 2) {
                $sellerProductIds = [];
                $shopUrl = '';
                if (isset($requestedData['shop']) && !empty($requestedData['shop'])) {
                    $shopUrl = $requestedData['shop'];
                    $data = $this->helperData->getSellerCollectionObjByShop($shopUrl);
                    $sellerId = '';
                    foreach ($data as $seller) {
                        $sellerId = $seller->getSellerId();
                    }
                    $querydata = $this->productCollection->create()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )
                    ->addFieldToFilter(
                        'status',
                        ['eq' => 1]
                    );
                    $sellerProductIds = $querydata->getAllIds();
                }
                $searchKey = strtolower($requestedData['q']);

                // Search record by sku if available
                $productId = $this->_product->create()->getIdBySku($searchKey);
                if ($productId) {
                    $responseData = $this->getProductDataById(
                        $productId,
                        $sellerProductIds,
                        $requestedData,
                        $responseData,
                        $shopUrl
                    );
                } else {
                    // Search record by name for all search keyword
                    $layer = $this->getLayer();
                    $collection = $layer->getProductCollection();
                    if ($shopUrl) {
                        $collection->addAttributeToFilter(
                            'entity_id',
                            ['in' => $sellerProductIds]
                        );
                    }
                    if (!isset($requestedData['rfq']) || empty($requestedData['rfq'])) {
                        $allowedProTypes = explode(',', $this->helperData->getAllowedProductType());
                        $collection->addAttributeToFilter(
                            'type_id',
                            ['in' => $allowedProTypes]
                        );
                    }
                    $collection->addAttributeToFilter(
                        'name',
                        ['like' => '%'.$searchKey.'%']
                    );
                    if ($collection->getSize()) {
                        $collection->addAttributeToSelect('*');
                        $collection->addAttributeToFilter('visibility', ['in' => [4]]);
                        $collection->addAttributeToFilter('status', 1);
                        $collection->setPageSize(20);
                        $collection->setCurPage(1);
                        $responseData['size'] = $collection->getSize();
                        $resultPage = $this->resultPageFactory->create();
                        $blockInstance = $resultPage->getLayout()->getBlock('supplier_product_search');
                        foreach ($collection as $product) {
                            $productModel = $this->_product->create()->load($product->getId());
                            $isOptions = 0;
                            $isOptions = $this->calculateIsOptions($product, $productModel);
                            $responseData['products'][] = [
                                'entity_id' => $product->getId(),
                                'name' => $product->getName(),
                                'sku' => $product->getSku(),
                                'price' => $product->getPrice(),
                                'is_options' => $isOptions,
                                'final_price' => $productModel->getFinalPrice(),
                                'price_html' => $blockInstance->getProductPrice($product),
                                'type_id' => $product->getTypeId()
                            ];
                        }
                    } else {
                        // Search record by name for any of the search keyword
                        $responseData = $this->getProductDataBySearchKeywords(
                            $searchKey,
                            $sellerProductIds,
                            $requestedData,
                            $responseData,
                            $shopUrl
                        );
                    }
                }
            }
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;
        } catch (\Exception $e) {
            $responseData = [
                'error' => $e->getMessage(),
                'errorcode' => $e->getCode(),
            ];
            /** @var \Magento\Framework\Controller\Result\Json $resultJson */
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $resultJson->setData($responseData);
            return $resultJson;
        }
    }

    public function calculateIsOptions($product, $productModel)
    {
        $isOptions = 0;
        if ($product->getTypeId() == 'configurable' || $productModel->getOptions()) {
            $isOptions = 1;
        } elseif ($product->getTypeId() == 'downloadable') {
            if ($productModel->getTypeInstance()->hasLinks($productModel)) {
                $isOptions = 1;
            }
        } elseif ($product->getTypeId() == 'bundle') {
            $isOptions = 1;
        } elseif ($product->getTypeId() == 'grouped') {
            $isOptions = 1;
        }
        return $isOptions;
    }

    /**
     * Return Seller's Product Search Data
     *
     * @param int $productId
     * @param array $sellerProductIds
     * @param array $requestedData
     * @param array $responseData
     * @param string $shopUrl
     * @return array
     */
    public function getProductDataById($productId, $sellerProductIds, $requestedData, $responseData, $shopUrl = '')
    {
        $flag = 1;
        if ($shopUrl) {
            if (!in_array($productId, $sellerProductIds)) {
                $flag = 0;
            }
        }
        if ($flag === 1) {
            $product = $this->_product->create()->load($productId);
            if ($product->isSaleable()) {
                $isOptions = 0;
                if ($product->getTypeId() == 'configurable' || $product->getOptions()) {
                    $isOptions = 1;
                } elseif ($product->getTypeId() == 'downloadable') {
                    if ($product->getTypeInstance()->hasLinks($product)) {
                        $isOptions = 1;
                    }
                } elseif ($product->getTypeId() == 'bundle') {
                    $isOptions = 1;
                } elseif ($product->getTypeId() == 'grouped') {
                    $isOptions = 1;
                }
                $resultPage = $this->resultPageFactory->create();
                $blockInstance = $resultPage->getLayout()->getBlock('supplier_product_search');
                $responseData['size'] = 1;
                $responseData['products'][] = [
                    'entity_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice(),
                    'is_options' => $isOptions,
                    'final_price' => $product->getFinalPrice(),
                    'price_html' => $blockInstance->getProductPrice($product),
                    'type_id' => $product->getTypeId()
                ];
            }
        }
        return $responseData;
    }

    /**
     * Return Seller's Product Search Data for all search keyword
     *
     * @param string $searchKey
     * @param array $sellerProductIds
     * @param array $requestedData
     * @param array $responseData
     * @param string $shopUrl
     * @return array
     */
    public function getProductDataBySearchKeywords(
        $searchKey,
        $sellerProductIds,
        $requestedData,
        $responseData,
        $shopUrl = ''
    ) {
        $searchKeyArr = explode(' ', $searchKey);
        foreach ($searchKeyArr as $key => $searchKey) {
            $collection = $this->catalogProductCollection->create();
            if ($shopUrl) {
                $collection->addAttributeToFilter(
                    'entity_id',
                    ['in' => $sellerProductIds]
                );
            }
            if (!isset($requestedData['rfq']) || empty($requestedData['rfq'])) {
                $allowedProTypes = explode(',', $this->helperData->getAllowedProductType());
                $collection->addAttributeToFilter(
                    'type_id',
                    ['in' => $allowedProTypes]
                );
            }
            $collection->addAttributeToFilter(
                'name',
                ['like' => '%'.$searchKey.'%']
            );
            $collection->addAttributeToSelect('*');
            $collection->addAttributeToFilter('visibility', ['in' => [4]]);
            $collection->addAttributeToFilter('status', 1);
            $collection->setPageSize(10);
            $collection->setCurPage(1);
            $responseData['size'] = $collection->getSize();
            $resultPage = $this->resultPageFactory->create();
            $blockInstance = $resultPage->getLayout()->getBlock('supplier_product_search');
            foreach ($collection as $product) {
                $productModel = $this->_product->create()->load($product->getId());
                $isOptions = 0;
                if ($product->getTypeId() == 'configurable' || $productModel->getOptions()) {
                    $isOptions = 1;
                } elseif ($product->getTypeId() == 'downloadable') {
                    if ($productModel->getTypeInstance()->hasLinks($productModel)) {
                        $isOptions = 1;
                    }
                } elseif ($product->getTypeId() == 'bundle') {
                    $isOptions = 1;
                } elseif ($product->getTypeId() == 'grouped') {
                    $isOptions = 1;
                }
                $responseData['products'][] = [
                    'entity_id' => $product->getId(),
                    'name' => $product->getName(),
                    'sku' => $product->getSku(),
                    'price' => $product->getPrice(),
                    'is_options' => $isOptions,
                    'final_price' => $productModel->getFinalPrice(),
                    'price_html' => $blockInstance->getProductPrice($product),
                    'type_id' => $product->getTypeId()
                ];
            }
        }

        return $responseData;
    }
}
