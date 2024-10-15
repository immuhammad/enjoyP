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

use Webkul\Mpmembership\Api\Data\TransactionInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Webkul Mpmembership model TransactionRepository
 */
class TransactionRepository implements
    \Webkul\Mpmembership\Api\TransactionRepositoryInterface
{
    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var Transaction[]
     */
    private $instances = [];

    /**
     * @var Transaction[]
     */
    private $instancesById = [];

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Transaction
     */
    private $resourceModel;

    /**
     * @var \Magento\Framework\Api\ExtensibleDataObjectConverter
     */
    private $extensibleDataObjectConverter;

    public function __construct(
        TransactionFactory $transactionFactory,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Model\ResourceModel\Transaction $resourceModel,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->collectionFactory = $collectionFactory;
        $this->resourceModel = $resourceModel;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($sellerId, $storeId = null)
    {
        $transactionData = $this->transactionFactory->create();
        $trId = '';
        $transactionCollection = $this->collectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId);
        foreach ($transactionCollection as $value) {
            $trId = $value->getId();
        }
        $transactionData->load($trId);
        $this->instances[$sellerId] = $transactionData;
        $this->instancesById[$transactionData->getId()] = $transactionData;

        return $this->instances[$sellerId];
    }

    /**
     * {@inheritdoc}
     */
    public function getById($id, $storeId = null)
    {
        $transactionData = $this->transactionFactory->create();
        $transactionData->load($id);
        $this->instancesById[$id] = $transactionData;
        $this->instances[$transactionData->getSellerId()] = $transactionData;

        return $this->instancesById[$id];
    }

    /**
     * {@inheritdoc}
     *
     */
    public function save(
        \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction,
        $saveOptions = false
    ) {
        $id = $transaction->getId();

        $transactionDataArray = $this->extensibleDataObjectConverter
            ->toNestedArray(
                $transaction,
                [],
                \Webkul\Mpmembership\Api\Data\TransactionInterface::class
            );

        $transaction = $this->initializeTransactionData(
            $transactionDataArray,
            empty($id)
        );

        try {
            $this->resourceModel->save($transaction);
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\CouldNotSaveException(
                __('Unable to save transaction')
            );
        }
        unset($this->instances[$transaction->getSellerId()]);
        unset($this->instancesById[$transaction->getId()]);

        return $this->get($transaction->getSellerId());
    }

    protected function initializeTransactionData(
        array $transactionDataArray,
        $createNew
    ) {
        if ($createNew) {
            $transaction = $this->transactionFactory->create();
        } else {
            if (!empty($transactionDataArray['seller_id'])) {
                unset($this->instances[$transactionDataArray['seller_id']]);
                $transaction = $this->get($transactionDataArray['seller_id']);
            } else {
                unset($this->instancesById[$transactionDataArray['id']]);
                $transaction = $this->getById($transactionDataArray['id']);
            }
        }
        foreach ($transactionDataArray as $key => $value) {
            $transaction->setData($key, $value);
        }

        return $transaction;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(
        \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction
    ) {
        $sellerId = $transaction->getSellerId();
        $id = $transaction->getId();
        try {
            $this->resourceModel->delete($transaction);
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
        $transaction = $this->getById($id);
        return $this->delete($transaction);
    }
}
