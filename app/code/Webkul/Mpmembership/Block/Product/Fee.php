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

namespace Webkul\Mpmembership\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul Mpmembership Product Fee Block
 */
class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Webkul\Mpmembership to return collection
     */
    protected $productList;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param CollectionFactory $mpCollectionFactory
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param \Webkul\Marketplace\Model\ResourceModel\Product\Collection $mpProductCollection
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        CollectionFactory $mpCollectionFactory,
        \Webkul\Mpmembership\Helper\Data $helper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory $mpProductCollection,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->productCollectionFactory = $mpCollectionFactory;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->eavAttribute = $eavAttribute;
        $this->mpProductCollection = $mpProductCollection;
        $this->jsonHelper = $jsonHelper;
        parent::__construct($context, $data);
    }

    /**
     * getAllProducts used to get all seller products which are disabled
     *
     * @return object [returns collection]
     */
    public function getAllProducts()
    {
        try {
            if (!$this->productList) {
                $data = $this->getParameters();

                $filterName = '';
                $filterSku  = '';

                if (isset($data['name'])) {
                    $filterName = $data['name'] != '' ? $data['name'] : '';
                }

                if (isset($data['sku'])) {
                    $filterSku = $data['sku'] != '' ? $data['sku'] : '';
                }

                $mpProductModel  = $this->productCollectionFactory->create();
                $userId          = $this->helper->getSellerId();
                $mpMembershipProduct = $this->mpProductCollection->create()->getTable(
                    'marketplace_membership_product'
                );
                $mpProductCollection = $this->productCollectionFactory->create();
                $mpProductCollection->getSelect()->join(
                    $mpMembershipProduct.' as mpme',
                    'main_table.seller_id = mpme.seller_id'
                )->where('FIND_IN_SET(main_table.mageproduct_id, mpme.product_ids) AND main_table.status=2');
                $productIds = $mpProductCollection->getAllIds();
                $collection = $mpProductModel
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq'  =>  $userId]
                    )->addFieldToFilter(
                        'status',
                        ['eq'  =>  2]
                    )->addFieldToSelect(
                        ['mageproduct_id']
                    );
                if (!empty($productIds)) {
                    $collection->addFieldToFilter(
                        'mageproduct_id',
                        ['nin' => $productIds]
                    );
                }
                if ($collection->getSize()) {
                    $proAttId = $this->eavAttribute->getIdByCode(
                        "catalog_product",
                        "name"
                    );

                    $catalogProductEntity = $this->mpProductCollection->create()->getTable(
                        'catalog_product_entity'
                    );

                    $catalogProductEntityVarchar = $this->mpProductCollection->create()->getTable(
                        'catalog_product_entity_varchar'
                    );
                    $collection->getSelect()->join(
                        $catalogProductEntity.' as cpe',
                        'main_table.mageproduct_id = cpe.entity_id'
                    )->where(
                        "cpe.sku like '%".$filterSku."%'"
                    );

                    $collection->getSelect()->join(
                        $catalogProductEntityVarchar.' as cpev',
                        'main_table.mageproduct_id = cpev.entity_id'
                    )->where(
                        'cpev.value like "%'.$filterName.'%" AND
                        cpev.attribute_id = '.$proAttId
                    );
                    $collection->getSelect()->group('mageproduct_id');
                    $this->productList = $collection;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getAllProducts Exception : ".$e->getMessage()
            );
        }
        return $this->productList;
    }

    /**
     * getProductFinalPrice get product's final price
     *
     * @param int $id [contains product id]
     * @return float [returns product final price]
     */
    public function getProductFinalPrice($id)
    {
        try {
            $_product = $this->productRepository->getById((int)$id);
            $finalPrice = $_product->getFinalPrice();
            if ($_product->getTypeId()=="configurable") {
                $price = null;
                foreach ($_product->getTypeInstance()->getUsedProducts($_product) as $product) {
                    $childPriceAmount = $product->getPriceInfo()->getPrice('regular_price')->getAmount();
                    $assPrice[] = $childPriceAmount->getValue();
                    $price = $price ? min($price, $childPriceAmount->getValue()) : $childPriceAmount->getValue();
                }
                if ($price) {
                    $finalPrice = max($assPrice);
                }
            }
            return $finalPrice;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getProductFinalPrice Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * _prepareLayout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        try {
            parent::_prepareLayout();
            if ($this->getAllProducts()) {
                $pager = $this->getLayout()->createBlock(
                    \Magento\Theme\Block\Html\Pager::class,
                    'mpmembership.product.list.pager'
                )->setCollection(
                    $this->getAllProducts()
                );
                $this->setChild('pager', $pager);
                $this->getAllProducts()->load();
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee _prepareLayout Exception : ".$e->getMessage()
            );
        }
        return $this;
    }

    /**
     * getPagerHtml
     *
     * @return object
     */
    public function getPagerHtml()
    {
        try {
            return $this->getChildHtml('pager');
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getPagerHtml Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getFeeAmount get fees amount depending on the percentage or fixed
     *
     * @param float $amount
     * @param int $counter
     * @return float [returns fee amount]
     */
    public function getFeeAmount($amount, $counter)
    {
        $feeAmount = 0;
        try {
            $feeType = $this->helper->getConfigFeeType();
            $fee     = $this->helper->getConfigFeeAmount();
            $fee = $this->helper->convertFromBaseToCurrentCurrency($fee);
            if ($feeType == 1) {
                $feeAmount = ($fee  * $counter);
            } else {
                $feeAmount = (($amount * $fee) / 100);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getFeeAmount Exception : ".$e->getMessage()
            );
        }
        return $feeAmount;
    }

    /**
     * getCurrentRates used to get current rates
     *
     * @return int|float [returns current currency rates]
     */
    public function getCurrentRates()
    {
        try {
            $baseCurrencyCode    = $this->helper->getBaseCurrencyCode();
            $currentCurrencyCode = $this->helper->getCurrentCurrencyCode();
            $allowedCurrencies   = $this->helper->getAllowedCurrencies();

            $rates = $this->helper->getCurrencyRates(
                $baseCurrencyCode,
                array_values($allowedCurrencies)
            );

            if (!$rates[$currentCurrencyCode]
                || $rates[$currentCurrencyCode] == ""
                || $rates[$currentCurrencyCode] == 0
                || $rates[$currentCurrencyCode] == null
            ) {
                $rates[$currentCurrencyCode] = 1;
            }

            return $rates[$currentCurrencyCode];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getCurrentRates Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getFeeData prepare data used to submit form to paypal to do payment
     *
     * @return array
     */
    public function getFeeData()
    {
        try {
            $sandboxStatus    = $this->helper->getConfigSandboxStatus();
            $defaultTime      = $this->helper->getConfigDefaultTime();
            $merchantPaypalId = $this->helper->getConfigPaypalId();
            $pendingIds = [];
            $defaultProducts = 0;
            $totalAmount = 0;

            $customerId = $this->helper->getSellerId();
            $customer   = $this->helper->getCustomerById($customerId);
            $email      = $customer->getEmail();
            $firstName  = $customer->getFirstname();
            $lastName   = $customer->getLastname();

            $unapprovedProduct = $this->getAllProducts();
            if (isset($unapprovedProduct) && count($unapprovedProduct) > 0) {
                $pendingIds = $this->helper->getPendingProductIds($customerId);
            }
            if (isset($unapprovedProduct) && (count($unapprovedProduct) >= count($pendingIds))) {
                $defaultProducts   = count($unapprovedProduct) - count($pendingIds);
            } else {
                $defaultProducts   = isset($unapprovedProduct) ? count($unapprovedProduct) : 0;
            }

            if (isset($unapprovedProduct)) {
                foreach ($unapprovedProduct as $product) {
                    $price = $this->getProductFinalPrice($product->getId());
                    if ($price) {
                        $totalAmount += $price;
                    }
                }
            }

            $feeAmount = $this->getFeeAmount(
                $totalAmount,
                $defaultProducts
            );
            if ($this->helper->getCurrentCurrencyCode() !== $this->helper->getBaseCurrencyCode()) {
                $feeAmount = $this->helper->getFormattedPrice(
                    $feeAmount
                );
            }
            $data = [
                'seller_fee'    => $feeAmount,
                'merchant'      => $merchantPaypalId,
                'currency_code' => $this->helper->getBaseCurrencyCode(),
                'customer_id'   => $customerId,
                'email'         => $email,
                'firstname'     => $firstName,
                'lastname'      => $lastName,
                'sandbox'       => $sandboxStatus,
                'products'      => $defaultProducts,
                'time'          => $defaultTime,
                'pending_product_ids' => $pendingIds
            ];

            return $data;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getFeeData Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getPostData get post data and submit to paypal
     *
     * @return array
     */
    public function getPostData()
    {
        try {
            $params = $this->getParameters();
            $defaultTime = $this->helper->getConfigDefaultTime();
            if ($this->helper->getCurrentCurrencyCode() !== $this->helper->getBaseCurrencyCode()) {
                $params['amount_1'] = $this->helper->formatPrice(
                    $params['amount_1'],
                    $this->helper->getCurrentCurrencyCode()
                );
                $params['currency_code'] = $this->helper->getBaseCurrencyCode();
            }
            $data = [
                'seller_fee'    => $params['amount_1'],
                'merchant'      => $params['business'],
                'currency_code' => $params['currency_code'],
                'invoice'       => $params['invoice'],
                'email'         => $params['email'],
                'firstname'     => $params['first_name'],
                'lastname'      => $params['last_name'],
                'sandbox'       => $params['sandbox_mode'],
                'products'      => $params['allowed_products'],
                'time'          => $defaultTime,
                'products_ids'  => json_encode($params['product_id'])
            ];
            return $data;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getPostData Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getIsSecure check is secure or not
     *
     * @return boolean
     */
    public function getIsSecure()
    {
        try {
            return $this->getRequest()->isSecure();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getIsSecure Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getJsonHelper
     *
     * @return \Magento\Framework\Json\Helper\Data
     */
    public function getJsonHelper()
    {
        return $this->jsonHelper;
    }

    /**
     * getParameters get request parameters
     *
     * @return array
     */
    public function getParameters()
    {
        try {
            return $this->getRequest()->getParams();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Fee_getParameters Exception : ".$e->getMessage()
            );
        }
    }
}
