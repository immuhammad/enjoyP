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

class FeaturedProducts extends \Webkul\Marketplace\Block\Profile
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
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->helperFactory = $helperFactory;
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
    public function getFeaturedProducts()
    {
        $products = [];
        $partner = $this->getProfileDetail();
        if ($partner && $partner->getSellerId()) {
            $catalogProductWebsite = $this->_objectManager->create(
                \Webkul\Marketplace\Model\ResourceModel\Product\Collection::class
            )->getTable('catalog_product_website');
            $helper = $this->_objectManager->create(
                \Webkul\Marketplace\Helper\Data::class
            );
            if (count($helper->getAllWebsites()) == 1) {
                $websiteId = 0;
            } else {
                $websiteId = $helper->getWebsiteId();
            }
            $querydata = $this->_objectManager->create(\Webkul\Marketplace\Model\Product::class)
                ->getCollection()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $partner->getSellerId()]
                )
                ->addFieldToFilter(
                    'status',
                    ['neq' => 2]
                )
                ->addFieldToSelect('mageproduct_id')
                ->setOrder('mageproduct_id');
            $ids = $querydata->getAllIds();
            $queryIds = [];
            foreach ($ids as $idVal) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $product = $objectManager->create(\Magento\Catalog\Model\Product::class)->load($idVal);
                if ($product->getIsFeaturedProduct() == "1") {
                    $queryIds[] = $product->getId();
                }
            }
            $products = $this->_objectManager->create(
                \Magento\Catalog\Model\Product::class
            )->getCollection();
            $products->addAttributeToSelect('*');
            $products->addAttributeToFilter('entity_id', ['in' => $queryIds]);
            $products->addAttributeToFilter('visibility', ['in' => [4]]);
            $products->addAttributeToFilter('status', 1);
            $products->addAttributeToFilter('is_featured_product', ['eq' => 1]);
            if ($websiteId) {
                $products->getSelect()
                ->join(
                    ['cpw' => $catalogProductWebsite],
                    'cpw.product_id = e.entity_id'
                )->where(
                    'cpw.website_id = '.$websiteId
                );
            }
            $products->setPageSize(4)->setCurPage(1)->setOrder('entity_id');
        }

        return $products;
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
