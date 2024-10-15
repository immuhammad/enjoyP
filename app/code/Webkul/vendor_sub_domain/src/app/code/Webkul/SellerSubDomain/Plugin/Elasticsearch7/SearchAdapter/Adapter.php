<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\SellerSubDomain\Plugin\Elasticsearch7\SearchAdapter;

use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\Framework\Search\RequestInterface;
use Magento\Elasticsearch\SearchAdapter\Aggregation\Builder as AggregationBuilder;
use Magento\Elasticsearch\SearchAdapter\ConnectionManager;
use Magento\Elasticsearch\SearchAdapter\ResponseFactory;
use Magento\Elasticsearch7\SearchAdapter\Mapper;
use Magento\Elasticsearch\SearchAdapter\QueryContainerFactory;

class Adapter
{

    /**
     * Response Factory
     *
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var ConnectionManager
     */
    private $connectionManager;

    /**
     * @var AggregationBuilder
     */
    private $aggregationBuilder;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $helper;
    /**
     * Mapper instance
     *
     * @var Mapper
     */
    private $mapper;

    /**
     * @var QueryContainerFactory
     */
    private $queryContainerFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $collection;

    /**
     * Empty response from Elasticsearch
     *
     * @var array
     */
    private static $emptyRawResponse = [
        "hits" =>
            [
                "hits" => []
            ],
        "aggregations" =>
            [
                "price_bucket" => [],
                "category_bucket" =>
                    [
                        "buckets" => []

                    ]
            ]
    ];

    /**
     * @param ConnectionManager $connectionManager
     * @param ResponseFactory $responseFactory
     * @param AggregationBuilder $aggregationBuilder
     * @param Mapper $mapper
     * @param \Webkul\Marketplace\Helper\Data $helper
     * @param QueryContainerFactory $queryContainerFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection
     */
    public function __construct(
        ConnectionManager $connectionManager,
        ResponseFactory $responseFactory,
        AggregationBuilder $aggregationBuilder,
        Mapper $mapper,
        QueryContainerFactory $queryContainerFactory,
        \Webkul\Marketplace\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $collection
    ) {
        $this->connectionManager = $connectionManager;
        $this->responseFactory = $responseFactory;
        $this->aggregationBuilder = $aggregationBuilder;
        $this->mapper = $mapper;
        $this->queryContainerFactory = $queryContainerFactory;
        $this->helper = $helper;
        $this->request = $request;
        $this->collection = $collection;
    }

    public function aroundQuery(
        \Magento\Elasticsearch7\SearchAdapter\Adapter $subject,
        callable $proceed,
        RequestInterface $request
    ) {
        if ($this->request->getFullActionName() == 'sellersubdomain_collection_index') {
            $marketplaceProduct = $this->collection->getTable('marketplace_product');
            $sellerId = $this->getProfileDetail()->getSellerId();
            $client = $this->connectionManager->getConnection();
            $aggregationBuilder = $this->aggregationBuilder;
            $updatedQuery = $this->mapper->buildQuery($request);
            $productIds = $this->helper->getSellerProducts($sellerId);
            $updatedQuery['body']['query']['bool']['filter'] = ['ids' => ['values' => $productIds]];
            $aggregationBuilder->setQuery($this->queryContainerFactory->create(['query' => $updatedQuery]));
            try {
                $rawResponse = $client->query($updatedQuery);
            } catch (\Exception $e) {
                $this->helper->logDataInLogger("Elasticsearch7_SearchAdapter_Adapter aroundQuery ".$e->getMessage());
                // return empty search result in case an exception is thrown from Elasticsearch
                $rawResponse = self::$emptyRawResponse;
            }

            $rawDocuments = $rawResponse['hits']['hits'] ?? [];
            $queryResponse = $this->responseFactory->create(
                [
                    'documents' => $rawDocuments,
                    'aggregations' => $aggregationBuilder->build($request, $rawResponse),
                    'total' => $rawResponse['hits']['total']['value'] ?? 0
                ]
            );
            return $queryResponse;
        }
        return $proceed($request);
    }

    /**
     * @return array
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->helper->getCollectionUrl();
        if (!$shopUrl) {
            $shopUrl = $this->request->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->helper->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }
}
