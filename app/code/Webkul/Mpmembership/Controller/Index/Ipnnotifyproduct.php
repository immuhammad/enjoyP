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

namespace Webkul\Mpmembership\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProductCollectionFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Mpmembership\Model\Product as ProductTransaction;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 *  Webkul Mpmembership Index Ipnnotifyproduct controller
 */
class Ipnnotifyproduct extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\Mpmembership\Model\Product
     */
    protected $membershipModel;

    /**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory
     */
    protected $mpProductCollectionFactory;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Mpmembership\Helper\Email
     */
    protected $emailHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @param Context $context
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     * @param SellerProductCollectionFactory $mpProductCollectionFactory
     * @param \Webkul\Mpmembership\Model\Product $membershipModel
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Mpmembership\Helper\Email $emailHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        \Webkul\Mpmembership\Helper\Data $helper,
        CollectionFactory $collectionFactory,
        SellerProductCollectionFactory $mpProductCollectionFactory,
        \Webkul\Mpmembership\Model\Product $membershipModel,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Mpmembership\Helper\Email $emailHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        parent::__construct($context);
        $this->helper     = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->mpProductCollectionFactory = $mpProductCollectionFactory;
        $this->membershipModel = $membershipModel;
        $this->productRepository = $productRepository;
        $this->date = $date;
        $this->emailHelper = $emailHelper;
        $this->marketplaceHelper = $marketplaceHelper;
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(
        RequestInterface $request
    ): ?InvalidRequestException {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * executes when IPN hit by paypal for product transaction
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct execute msg => notify:seller membership product notification"
            );
            $currencySymbol = "";
            $feeAmount = 0;
            $transdata = [];
            $data = $this->getRequest()->getParams();
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct execute data => ".json_encode($data)
            );

            if (!empty($data)
                && !empty($data['payment_status'])
                && strtolower($data['payment_status']) == 'completed'
            ) {
                $invoice = explode('-', $data['invoice']);
                $referenceNumber = $invoice[0];
                $sellerId = $invoice[1];

                $productIds = json_decode(urldecode($data['custom']));

                if (isset($data['mc_currency'])) {
                    $currencySymbol = $this->helper->getCurrencySymbol($data['mc_currency']);
                }
                if (isset($data['mc_gross'])) {
                    $feeAmount = $currencySymbol.$data['mc_gross'];
                }
                /* $data['payment_date'] = $this->helper->convertDateTimeToConfigTimeZone(
                    $data['payment_date']
                ); */
                $data['payment_date'] = strtotime($data['payment_date']);
                $transdata = [
                    'seller_id' => $sellerId,
                    'transaction_id' => $data['txn_id'],
                    'transaction_email' => $data['payer_email'],
                    'ipn_transaction_id' => $data['ipn_track_id'],
                    'transaction_date' => $data['payment_date'],
                    'no_of_products' => count($productIds),
                    'product_ids' => implode(",", $productIds),
                    'transaction_status' => $data['payer_status'],
                    'reference_number' => $referenceNumber
                ];
                $result = $this->checkSellerProductTransaction($transdata);

                if ($result['flag'] && $data['payer_status'] == 'verified') {
                    $this->enableProduct($productIds, $sellerId);
                    $sellerTransaction = $this->membershipModel->load($result['entityId']);
                    $this->emailHelper->sendTransactionEmailToSeller(
                        $this->prepareEmailVariables($sellerTransaction, $feeAmount, $productIds),
                        $this->getSenderInfo(),
                        $this->getReceiverInfo($sellerTransaction)
                    );
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Controller_Index_Ipnnotifyproduct execute : ".$e->getMessage());
        }
    }

    /**
     * getSenderInfo
     *
     * @return array
     */
    private function getSenderInfo()
    {
        $senderInfo = [];
        try {
            $adminStoremail = $this->marketplaceHelper->getAdminEmailId();
            $defaultTransEmailId = $this->marketplaceHelper->getDefaultTransEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
            $adminUsername = $this->marketplaceHelper->getAdminName();
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_getSenderInfo Exception : ".$e->getMessage()
            );
        }
        return $senderInfo;
    }

    /**
     * getReceiverInfo
     *
     * @param \Webkul\Mpmembership\Model\Product $sellerTransaction
     * @return array
     */
    private function getReceiverInfo($sellerTransaction)
    {
        $receiverInfo = [];
        try {
            $sellerId = $sellerTransaction->getSellerId();
            $userdata = $this->helper->getCustomerById($sellerId);
            $username = $userdata->getFirstname()." ".$userdata->getLastname();
            $useremail = $userdata->getEmail();
            $receiverInfo = [
                'name' => $username,
                'email' => $useremail,
            ];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_getReceiverInfo Exception : ".$e->getMessage()
            );
        }
        return $receiverInfo;
    }

    /**
     * prepareEmailVariables
     *
     * @param \Webkul\Mpmembership\Model\Product $sellerTransaction
     * @param int|float $amount
     * @param array $productIds
     * @return array
     */
    private function prepareEmailVariables($sellerTransaction, $amount, $productIds)
    {
        $emailTempVariables = [];
        try {
            $transactionHtml = "";
            $productNames = [];

            foreach ($productIds as $productId) {
                $productNames[] = $this->productRepository->getById($productId)->getName();
            }

            $emailTempVariables['transaction_id'] = $sellerTransaction->getTransactionId();
            $emailTempVariables['fee_amount'] = $amount;
            if (!empty($productNames)) {
                $transactionHtml .= "<tr>
                                <td class='query-details'>
                                    <h3><b>".__('Following Product(s) have been enabled')."</b></h3>
                                    <p>".implode(",", $productNames)."</p>
                                </td>
                            </tr>";
            }

            $emailTempVariables['myvarcustom'] = $transactionHtml;
            $userdata = $this->helper->getCustomerById($sellerTransaction->getSellerId());
            $username = $userdata->getFirstname()." ".$userdata->getLastname();
            $emailTempVariables['seller_name'] = $username;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_prepareEmailVariables Exception : ".$e->getMessage()
            );
        }
        return $emailTempVariables;
    }

    /**
     * enableProduct enable product status
     *
     * @param array $ids [contains product ids]
     * @param integer $sellerId [contains seller id]
     * @return void
     */
    private function enableProduct($ids, $sellerId)
    {
        try {
            $collection = $this->mpProductCollectionFactory->create();
            $productdata = $collection->addFieldToFilter(
                'seller_id',
                ['eq' => $sellerId]
            )->addFieldToFilter(
                'mageproduct_id',
                ['in' => $ids]
            );

            if ($productdata->getSize() > 0) {
                foreach ($productdata as $product) {
                    $product->setStatus(1);
                    $product->setIsApproved(1);
                    $product->setPaymentStatus(ProductTransaction::FEE_PAID);
                    $product->save();

                    $this->checkChildProducts($product->getMageproductId(), $sellerId);

                    $this->setProductStatus($product->getMageproductId());
                }
            } else {
                foreach ($ids as $product) {
                    $this->setProductStatus($product);
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_enableProduct Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * setProductStatus sets catalog product status
     *
     * @param int $id [contains product id]
     */
    private function setProductStatus($id)
    {
        try {
            $_product = $this->productRepository->getById($id);
            $_product->setStatus(Status::STATUS_ENABLED);
            $this->productRepository->save($_product);
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_setProductStatus Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * checkChildProducts
     *
     * @param int $id [product id]
     * @param int $sellerId
     * @return void
     */
    private function checkChildProducts($id, $sellerId)
    {
        try {
            $usedProductIds = [];
            $product = $this->productRepository->getById($id);
            if ($product->getTypeId()=="configurable") {
                $productTypeInstance = $product->getTypeInstance();
                $usedProductIds = $productTypeInstance->getUsedProductIds($product);
            }

            if (!empty($usedProductIds)) {
                $this->enableProduct($usedProductIds, $sellerId);
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_checkChildProducts Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * checkSellerProductTransaction
     *
     * @param array $data
     * @return array
     */
    private function checkSellerProductTransaction($data)
    {
        $flag = false;
        $entityId = 0;
        $returnArr = [];
        try {
            $collection = $this->collectionFactory->create()
                ->addFieldToFilter(
                    'seller_id',
                    ['eq' => $data['seller_id']]
                )->addFieldToFilter(
                    'reference_number',
                    ['eq' => $data['reference_number']]
                );
            if ($collection->getSize() > 0) {
                foreach ($collection as $transactionData) {
                    if ($transactionData->getTransactionId()==""
                        || $transactionData->getTransactionStatus()=="pending"
                    ) {
                        $data['updated_at'] = $this->date->gmtDate();
                        $entityId = $transactionData->getId();
                        $this->membershipModel->load($entityId)
                            ->addData($data)
                            ->save();
                        $flag = true;
                    }
                }
            } else {
                $sellerTransaction = $this->membershipModel
                    ->setData($data)
                    ->save();
                $entityId = $sellerTransaction->getId();
                $flag = true;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyproduct_checkSellerProductTransaction Exception : ".$e->getMessage()
            );
        }
        $returnArr['flag'] = $flag;
        $returnArr['entityId'] = $entityId;
        return $returnArr;
    }
}
