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

namespace Webkul\Mpmembership\Api;

/**
 * @api
 */
interface TransactionRepositoryInterface
{
    /**
     * Create product transaction
     *
     * @param \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction
     * @param bool                                               $saveOptions
     *
     * @return \Webkul\Mpmembership\Api\Data\TransactionInterface
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction,
        $saveOptions = false
    );

    /**
     * Get info about seller's product transaction by seller ID
     *
     * @param int      $sellerId
     * @param int|null $storeId
     *
     * @return \Webkul\Mpmembership\Api\Data\TransactionInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sellerId, $storeId = null);

    /**
     * Get info about product transaction by entity id
     *
     * @param int      $entityId
     * @param int|null $storeId
     *
     * @return \Webkul\Mpmembership\Api\Data\TransactionInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $storeId = null);

    /**
     * Delete product transaction
     *
     * @param \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(
        \Webkul\Mpmembership\Api\Data\TransactionInterface $transaction
    );

    /**
     * @param int $entityId
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($entityId);
}
