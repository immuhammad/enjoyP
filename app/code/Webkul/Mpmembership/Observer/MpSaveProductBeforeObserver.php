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

namespace Webkul\Mpmembership\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;
use Magento\Catalog\Model\ProductFactory;

/**
 * Webkul Mpmembership Observer MpSaveProductBeforeObserver
 */
class MpSaveProductBeforeObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        ProductFactory $productModel,
        \Magento\Framework\App\RequestInterface $requestInterface
    ) {
        $this->helper = $helper;
        $this->productModel = $productModel;
        $this->requestInterface = $requestInterface;
    }

    /**
     * Marketplace Product save before observer that checks,
     * if any pack is expired or not and if expired then throws exception
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer->getEvent()->getData();
        $productId = $this->requestInterface->getParam('id');
        if ($this->helper->isModuleEnabled()
            && (!$productId || $data[0]['type']=="configurable")
        ) {

            $associatedProductsCount = !$productId ? 1 : 0;

            if (isset($data[0]['type'])
                && $data[0]['type']=="configurable"
                && isset($data[0]['associated_product_ids'])
            ) {
                $alreadyUsedAssProducts = 0;
                if ($productId) {
                    $configProduct = $this->productModel->create()->load($productId);
                    $productType = $configProduct->getTypeId();
                    if ($productType == 'configurable') {
                        $usedProducts = $configProduct->getTypeInstance()->getUsedProducts($configProduct);
                        $alreadyUsedAssProducts = count($usedProducts);
                    }
                }
                $associatedProductIds = $data[0]['associated_product_ids'];
                $associatedProductsCount += (count($associatedProductIds) - $alreadyUsedAssProducts);
            }

            $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();
            if ($feeAppliedFor == Feeapplied::PER_VENDOR) {
                $result = $this->helper->getPermission();
                if (isset($result['expire']) && $result['expire'] && $associatedProductsCount) {
                    throw new LocalizedException(
                        __(
                            'please pay fee to add products'
                        )
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
}
