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

namespace Webkul\Mpquotesystem\Plugin\Catalog\Block\Product;

class ListProduct
{
    /**
     * @var \Webkul\Mpquotesystem\Helper\Data
     */
    protected $quoteHelper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $_productInfo;

    /**
     *  Initialize dependencies
     *
     * @param \Webkul\Mpquotesystem\Helper\Data $quoteHelper
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Webkul\Mpquotesystem\Helper\Data $quoteHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->quoteHelper = $quoteHelper;
        $this->repository = $productRepository;
    }
    
    /**
     * Change Product Price
     *
     * @param \Magento\Catalog\Block\Product\ListProduct        $subject
     * @param \Magento\Framework\Controller\Result\Redirect     $result
     */
    public function afterGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $subject,
        $result
    ) {
        try {
            $showPrice = (int)$this->quoteHelper->getConfigShowPrice();
            $modStatus = $this->quoteHelper->getQuoteEnabled();
            $id = $this->_productInfo->getEntityId();
            $quoteStatus = $this->repository->getById($id)->getQuoteStatus();
            if ($modStatus && ($quoteStatus == 1) && !$showPrice) {
                return $this->quoteHelper->removePriceInfo($result);
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $result;
    }

    /**
     * BeforeGetProductPrice plugin to assign the product model to a variable
     *
     * @param  \Magento\Catalog\Block\Product\ListProduct   $listProduct
     * @param  \Magento\Catalog\Model\Product               $product
     */
    public function beforeGetProductPrice(
        \Magento\Catalog\Block\Product\ListProduct $listProduct,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->_productInfo = $product ;
    }
}
