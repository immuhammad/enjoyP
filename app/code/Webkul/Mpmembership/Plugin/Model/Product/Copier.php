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
namespace Webkul\Mpmembership\Plugin\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

class Copier
{
    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $requestInterface,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->helper = $helper;
        $this->requestInterface = $requestInterface;
        $this->mpHelper = $mpHelper;
    }

    /**
     * beforeCopy
     *
     * @param \Magento\Catalog\Model\Product\Copier $subject
     * @param Product $product
     *
     * @return void
     */
    public function beforeCopy(
        \Magento\Catalog\Model\Product\Copier $subject,
        Product $product
    ) {
        $params = $this->requestInterface->getParams();
        $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();
        $flag = false;
        $associatedProductsCount = 1;

        if ($this->helper->isModuleEnabled()
            && $feeAppliedFor == Feeapplied::PER_VENDOR
            && !empty($params['back'])
            && $params['back'] == 'duplicate'
            && $product->getId()
        ) {
            $sellerProductData = $this->mpHelper->getSellerProductDataByProductId(
                $product->getId()
            );
            if ($sellerProductData && $sellerProductData->getSize()) {
                foreach ($sellerProductData as $productData) {
                    if ($productData->getSellerId() == $this->helper->getSellerId()) {
                        $flag = true;
                        break;
                    }
                }
            }
        }
        if ($flag) {
            $result = $this->helper->getPermission();

            if (!empty($params['type'])
                && $params['type']=="configurable"
                && isset($params['associated_product_ids'])
            ) {
                $associatedProductIds = $params['associated_product_ids'];
                $associatedProductsCount += count($associatedProductIds);
            }
            
            if (isset($result['expire']) && $result['expire']) {
                throw new LocalizedException(
                    __('please pay fee to add more products')
                );
            } else {
                if (isset($associatedProductIds)
                    && isset($result['check'])
                    && ($result['check']==0 || $result['check']==2)
                    && isset($result['qty'])
                    && $result['qty'] < $associatedProductsCount
                ) {
                    throw new LocalizedException(
                        __(
                            'you are allowed only %1 product(s) to add, please pay fee to add more products',
                            $result['qty']
                        )
                    );
                }
            }
        }
    }
}
