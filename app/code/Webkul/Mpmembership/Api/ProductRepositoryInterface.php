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
interface ProductRepositoryInterface
{
    /**
     * Create seller transaction
     *
     * @param \Webkul\Mpmembership\Api\Data\ProductInterface $product
     * @param bool                                           $saveOptions
     *
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface
     *
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Webkul\Mpmembership\Api\Data\ProductInterface $product,
        $saveOptions = false
    );

    /**
     * Get info about seller transaction by seller id
     *
     * @param int      $sellerId
     * @param int|null $storeId
     *
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sellerId, $storeId = null);

    /**
     * Get info about seller transaction by entity id
     *
     * @param int      $entityId
     * @param int|null $storeId
     *
     * @return \Webkul\Mpmembership\Api\Data\ProductInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($entityId, $storeId = null);

    /**
     * Delete seller transaction
     *
     * @param \Webkul\Mpmembership\Api\Data\ProductInterface $product
     *
     * @return bool Will returned True if deleted
     * @throws \Magento\Framework\Exception\StateException
     */
    public function delete(
        \Webkul\Mpmembership\Api\Data\ProductInterface $product
    );

    /**
     * Delete row by its auto id
     *
     * @param int $entityId contains auto id
     *
     * @return bool Will returned True if deleted
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\StateException
     */
    public function deleteById($entityId);
}
