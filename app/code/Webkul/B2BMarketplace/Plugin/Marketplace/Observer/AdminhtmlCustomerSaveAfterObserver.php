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
namespace Webkul\B2BMarketplace\Plugin\Marketplace\Observer;

use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

class AdminhtmlCustomerSaveAfterObserver
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ProductCollectionFactory
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager,
     * @param ProductCollectionFactory                  $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface  $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        ProductCollectionFactory $sellerProduct,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_objectManager = $objectManager;
        $this->_sellerProduct = $sellerProduct;
        $this->_jsonDecoder = $jsonDecoder;
    }

    public function beforeAssignProduct(
        \Webkul\Marketplace\Observer\AdminhtmlCustomerSaveAfterObserver $subject,
        $sellerId,
        $productIds
    ) {
        try {
            $productids = array_flip($this->_jsonDecoder->decode($productIds));

            // get all seller's products
            $sellerProductIds = $this->_sellerProduct->create()
            ->getAllAssignProducts(
                '`seller_id`='.$sellerId
            );

            $additionalProductIds = array_diff(array_values($productids), array_values($sellerProductIds));

            $this->_objectManager->get(
                \Magento\Catalog\Model\Product\Action::class
            )->updateAttributes($additionalProductIds, ['is_featured_product' => 0], 0);
        } catch (\Exception $e) {
            $e->getMessage();
        }
    }
}
