<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_Mpmembership
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpmembership\Model;

use Webkul\Mpmembership\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul Mpmembership model ProductRepository
 */
class ProductRepository implements
    \Webkul\Mpmembership\Api\ProductRepositoryInterface
{
    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var Product[]
     */
    private $instances = [];

    /**
     * @var Product[]
     */
    private $instancesById = [];

    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Product
     */
    private $resourceModel;

    /**
     * @var ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    /**
     * @param ProductFactory                $productFactory
     * @param CollectionFactory             $collectionFactory
     * @param Product                       $resourceModel
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ProductFactory $productFactory,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Model\ResourceModel\Product $resourceModel,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->productFactory = $productFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sellerId, $storeId = null)
    {
        $proTransactionData = $this->productFactory->create();
        $trId = '';
        $proTransactionCollection = $this->collectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($proTransactionCollection as $value) {
            $trId = $value->getId();
        }
        $proTransactionData->load($trId);
        $this->instances[$sellerId] = $proTransactionData;
        $this->instancesById[
            $proTransactionData->getId()
        ] = $proTransactionData;

        return $this->instances[$sellerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id, $storeId = null)
    {
        $proTransactionData = $this->productFactory->create();
        $proTransactionData->load($id);
        $this->instancesById[$id] = $proTransactionData;
        $this->instances[
            $proTransactionData->getSellerId()
        ] = $proTransactionData;

        return $this->instancesById[$id];
    }

    /**
     * {@inheritdoc}
     *
     */
    public function save(
        \Webkul\Mpmembership\Api\Data\ProductInterface $product,
        $saveOptions = false
    ) {
        $id = $product->getId();

        $productDataArray = $this->extensibleDataObjectConverter
            ->toNestedArray(
                $product,
                [],
                \Webkul\Mpmembership\Api\Data\ProductInterface::class
            );

        $product = $this->initializeProductData($productDataArray, empty($id));

        try {
            $this->resourceModel->save($product);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save transaction')
            );
        }
        unset($this->instances[$product->getSellerId()]);
        unset($this->instancesById[$product->getId()]);

        return $this->get($product->getSellerId());
    }

    /**
     * Merge data from DB and updates from request.
     *
     * @param array $productDataArray
     * @param bool  $createNew
     *
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface|Product
     */
    protected function initializeProductData(
        array $productDataArray,
        $createNew
    ) {
        if ($createNew) {
            $product = $this->productFactory->create();
        } else {
            unset($this->instances[$productDataArray['seller_id']]);
            $product = $this->get($productDataArray['seller_id']);
        }
        foreach ($productDataArray as $key => $value) {
            $product->setData($key, $value);
        }

        return $product;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\Mpmembership\Api\Data\ProductInterface $product
    ) {
        $sellerId = $product->getSellerId();
        $id = $product->getId();
        try {
            $this->resourceModel->delete($product);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\StateException(
                __('Unable to remove transaction %1', $sellerId)
            );
        }
        unset($this->instances[$sellerId]);
        unset($this->instancesById[$id]);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($id)
    {
        $product = $this->getById($id);
        return $this->delete($product);
    }
}
