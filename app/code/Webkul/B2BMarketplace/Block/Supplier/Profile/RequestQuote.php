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

namespace Webkul\B2BMarketplace\Block\Supplier\Profile;

use \Magento\Customer\Model\Customer;
use \Magento\Customer\Model\Session;
use \Webkul\Marketplace\Helper\Data as MpHelper;
use \Webkul\Marketplace\Model\FeedbackFactory;
use \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;
use \Webkul\Marketplace\Model\ProductFactory as MpProductModel;
use \Magento\Catalog\Model\ProductFactory;

class RequestQuote extends \Webkul\Marketplace\Block\Profile
{
    private $_objectManager;

    protected $helperFactory;

    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Framework\Url\Helper\Data $urlHelper,
        Customer $customer,
        Session $session,
        \Magento\Framework\Stdlib\StringUtils $stringUtils,
        MpHelper $mpHelper,
        FeedbackFactory $feedbackModel,
        CollectionFactory $mpProductCollection,
        MpProductModel $mpProductModel,
        ProductFactory $productFactory,
        \Magento\Framework\ObjectManagerInterface $helperFactory,
        \Webkul\Marketplace\Model\ResourceModel\SellerFlagReason\CollectionFactory $reasonCollection,
        array $data = []
    ) {
        $this->helperFactory = $helperFactory;
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        parent::__construct(
            $context,
            $postDataHelper,
            $urlHelper,
            $customer,
            $session,
            $stringUtils,
            $mpHelper,
            $feedbackModel,
            $mpProductCollection,
            $mpProductModel,
            $productFactory,
            $data,
            $reasonCollection
        );
    }
    /**
     * getProfileDetail
     *
     * @return \Webkul\Marketplace\Model\Seller||null
     */
    public function getProfileDetail($value = '')
    {
        $shopUrl = $this->_objectManager->create(
            'Webkul\B2BMarketplace\Helper\MpHelper'
        )->getRequestQuoteUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        if ($shopUrl) {
            $data = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            )->getSellerCollectionObjByShop($shopUrl);
            foreach ($data as $seller) {
                return $seller;
            }
        }
    }

    /**
     * getPriceFormat
     *
     * @return string
     */
    public function getPriceFormat()
    {
        return $this->_objectManager->create(
            \Magento\Framework\Locale\Format::class
        )->getPriceFormat();
    }

    /**
     * getRequestProductForQuote
     *
     * @param int $supplierId
     * @return \Magento\Catalog\Api\Data\ProductInterface||null
     */
    public function getRequestProductForQuote($supplierId)
    {
        if ($productId  = $this->getRequest()->getParam('product')) {
            try {
                // Get current product by Product Id
                $product = $this->_objectManager->create(
                    \Magento\Catalog\Api\ProductRepositoryInterface::class
                )->getById($productId);

                // Check if current product is of correct supplier
                $productSupplierId = 0;
                $marketplaceProduct = $this->_objectManager->create(
                    \Webkul\Marketplace\Helper\Data::class
                )->getSellerProductDataByProductId($productId);
                foreach ($marketplaceProduct as $value) {
                    $productSupplierId = $value['seller_id'];
                }
                if ($productSupplierId == $supplierId) {
                    return $product;
                } else {
                    return null;
                }
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }

    /**
     * @param String $className
     * @return object
     */
    public function helper($className)
    {
        $helper = $this->helperFactory->get($className);
        if (false === $helper instanceof \Magento\Framework\App\Helper\AbstractHelper) {
            throw new \LogicException($className . ' doesn\'t extends Magento\Framework\App\Helper\AbstractHelper');
        }
        return $helper;
    }
}
