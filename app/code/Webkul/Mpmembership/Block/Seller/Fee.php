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

namespace Webkul\Mpmembership\Block\Seller;

use Magento\Framework\View\Element\Template\Context;
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;

/**
 * Webkul Mpmembership Seller Fee Block
 */
class Fee extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\Mpmembership to return collection
     */
    protected $productList;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Helper\Data $helper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper                   = $helper;
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
                $transactionModel = $this->collectionFactory->create();
                $userId = $this->helper->getSellerId();

                $collection = $transactionModel
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $userId]
                );

                if ($collection->getSize()) {
                    $this->productList = $collection;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Fee_getAllProducts Exception : ".$e->getMessage()
            );
        }
        return $this->productList;
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
                "Block_Seller_Fee _prepareLayout Exception : ".$e->getMessage()
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
                "Block_Seller_Fee_getPagerHtml Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getFeeAmount get fees amount of seller
     *
     * @return float [returns fee amount]
     */
    public function getFeeAmount()
    {
        try {
            $fee = $this->helper->getConfigFeeAmount();
            // $feeAmount = ($fee / $this->getCurrentRates());
            $feeAmount = $this->helper->convertFromBaseToCurrentCurrency($fee);
            return $feeAmount;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Fee_getFeeAmount Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getCurrentRates used to get current rates
     *
     * @return int|float [returns current currency rates]
     */
    public function getCurrentRates()
    {
        try {
            $baseCurrencyCode = $this->helper->getBaseCurrencyCode();
            $currentCurrencyCode = $this->helper->getCurrentCurrencyCode();
            $allowedCurrencies = $this->helper->getAllowedCurrencies();
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
                "Block_Seller_Fee_getCurrentRates Exception : ".$e->getMessage()
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
            $feeAmount = $this->getFeeAmount();
            $sandboxStatus = $this->helper->getConfigSandboxStatus();
            $defaultTime = $this->helper->getConfigDefaultTime();
            $defaultProducts = $this->helper->getConfigDefaultProduct();
            $merchantPaypalId = $this->helper->getConfigPaypalId();

            $customerId = $this->helper->getSellerId();
            $customer = $this->helper->getCustomerById($customerId);
            $email = $customer->getEmail();
            $firstName = $customer->getFirstname();
            $lastName = $customer->getLastname();

            if ($this->helper->getCurrentCurrencyCode() !== $this->helper->getBaseCurrencyCode()) {
                $feeAmount = $this->helper->formatPrice(
                    $feeAmount,
                    $this->helper->getCurrentCurrencyCode()
                );
            }
            $data = [
                'seller_fee' => $feeAmount,
                'merchant' => $merchantPaypalId,
                'currency_code' => $this->helper->getBaseCurrencyCode(),
                'customer_id' => $customerId,
                'email' => $email,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'sandbox' => $sandboxStatus,
                'products' => $defaultProducts,
                'time' => $defaultTime
            ];

            return $data;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Fee_getFeeData Exception : ".$e->getMessage()
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
                "Block_Seller_Fee_getIsSecure Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * get Params data
     *
     * @return mixed
     */
    public function getParams()
    {
        return $this->getRequest()->getParams();
    }
}
