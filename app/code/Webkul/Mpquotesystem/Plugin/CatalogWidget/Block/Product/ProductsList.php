<?php
/**
 * Webkul
 *
 * @category  Webkul
 * @package   Webkul_Mpquotesystem
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Mpquotesystem\Plugin\CatalogWidget\Block\Product;

class ProductsList
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @var array
     */
    private $_productInfo;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->repository = $productRepository;
        $this->registry = $registry;
    }

    /**
     * After Get Product Price Html
     *
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $subject
     * @param array                                             $result
     * @return array
     */
    public function afterGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $subject,
        $result
    ) {
        try {
            $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
            $id = $this->_productInfo->getEntityId();
            $quoteStatus = $this->repository->getById($id)->getQuoteStatus();
            $modStatus = $this->quoteHelper->getQuoteEnabled();
        
            if (($quoteStatus == 1)) {
                $productInfo = $this->getQuoteProduct($id);
                //print_r($productInfo);
                $quoteItems = $this->registry->registry("quoteitems");
                if ($quoteItems) {
                    $tmpArray = $quoteItems[0]+$productInfo;
                    $quoteItems = [];
                    array_push($quoteItems, $tmpArray);
                    $this->registry->unregister("quoteitems");
                    $this->registry->register("quoteitems", $quoteItems);
                } else {
                    $tmpArray = [];
                    array_push($tmpArray, $productInfo);
                    $this->registry->register("quoteitems", $tmpArray);
                }
            }
        
            if ($modStatus && !$showPrice) {
                $result = $this->quoteHelper->removePriceInfo($result);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        
        return $result;
    }

    /**
     * Get quoted product
     *
     * @param int $id
     *
     * @return array
     */
    public function getQuoteProduct($id)
    {
        $auctionModuleEnabledOrNot = $this->quoteHelper->checkModuleIsEnabledOrNot('Webkul_Auction');
        $quoteProductsInfo = [];
        $productData = $this->repository->getById($id);
        $auctionCheck = 1;
        if ($auctionModuleEnabledOrNot) {
            $auctionValues = $productData->getAuctionType();
            $auctionOpt = explode(',', $auctionValues);
            if (in_array(2, $auctionOpt)) {
                $auctionCheck = 0;
            }
        }
        if ($auctionCheck) {
            $productUrl = $productData->getUrlModel()->getUrl($productData, ['_ignore_category' => true]);
            
            if (!$productData->getTypeInstance()->isPossibleBuyFromList($productData)) {
                $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                $quoteProductsInfo[$productData->getId()]['status'] = 0;
            } else {
                $minqty = $productData->getMinQuoteQty();
                if ($minqty=='' || $minqty==null) {
                    $minqty = $this->quoteHelper->getConfigMinQty();
                }
                $quoteProductsInfo[$productData->getId()]['min_qty'] = $minqty;
                $quoteProductsInfo[$productData->getId()]['url'] = $productUrl;
                $quoteProductsInfo[$productData->getId()]['status'] = 1;
            }
        }
        return $quoteProductsInfo;
    }

    /**
     * BeforeGetProductPrice plugin to assign the product model to a variable
     *
     * @param \Magento\CatalogWidget\Block\Product\ProductsList $listProduct
     * @param \Magento\Catalog\Model\Product                    $product
     *
     * @return \Magento\Catalog\Model\Product
     */
    public function beforeGetProductPriceHtml(
        \Magento\CatalogWidget\Block\Product\ProductsList $listProduct,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_productInfo = $product ;
    }
}
