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
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory as SellerCollectionFactory;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Mpmembership\Model\Transaction;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

/**
 *  Webkul Mpmembership Index Ipnnotifyseller controller
 */
class Ipnnotifyseller extends Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var SellerCollectionFactory
     */
    protected $sellerCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Model\Seller
     */
    protected $sellerModel;

    /**
     * @var Transaction
     */
    protected $membershipModel;

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
     * @param SellerCollectionFactory $sellerCollectionFactory
     * @param \Webkul\Marketplace\Model\Seller $sellerModel
     * @param Transaction $membershipModel
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Mpmembership\Helper\Email $emailHelper
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        \Webkul\Mpmembership\Helper\Data $helper,
        CollectionFactory $collectionFactory,
        SellerCollectionFactory $sellerCollectionFactory,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        Transaction $membershipModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Mpmembership\Helper\Email $emailHelper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        parent::__construct($context);
        $this->helper     = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->sellerCollectionFactory = $sellerCollectionFactory;
        $this->sellerModel = $sellerModel;
        $this->membershipModel = $membershipModel;
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
     * To proceed to checkout a selected cart
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller execute msg => notify:seller membership notification"
            );
            $currencySymbol = "";
            $feeAmount = 0;
            $transdata = [];
            $data = $this->getRequest()->getParams();
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller execute data => ".json_encode($data)
            );

            if (!empty($data)
                && !empty($data['payment_status'])
                && strtolower($data['payment_status']) == 'completed'
            ) {
                $invoice = explode('-', $data['invoice']);
                $referenceNumber = $invoice[0];
                $sellerId = $invoice[1];
                $mix = explode('-', $data['custom']);
                $noOfProducts = $mix[0];
                $checkType = $mix[1];

                $allowedTime = $this->helper->getConfigDefaultTime();

                /* $data['payment_date'] = $this->helper->convertDateTimeToConfigTimeZone(
                    $data['payment_date']
                ); */
                $data['payment_date'] = strtotime($data['payment_date']);

                // calculate expiry date
                if ($allowedTime && $allowedTime !== null) {
                    $expiryDate = strtotime('+'.$allowedTime.' months', $data['payment_date']);
                } else {
                    $expiryDate = time();
                }
                if ($checkType==2) {
                    $expiryDate = null;
                }

                if (isset($data['mc_currency'])) {
                    $currencySymbol = $this->helper->getCurrencySymbol($data['mc_currency']);
                }
                if (isset($data['mc_gross'])) {
                    $feeAmount = $currencySymbol.$data['mc_gross'];
                }

                $transdata = [
                    'seller_id' => $sellerId,
                    'transaction_id' => $data['txn_id'],
                    'transaction_email' => $data['payer_email'],
                    'ipn_transaction_id' => $data['ipn_track_id'],
                    'transaction_date' => $data['payment_date'],
                    'no_of_products' => $noOfProducts,
                    'check_type' => $checkType,
                    'expiry_date' => $expiryDate,
                    'transaction_status' => $data['payer_status'],
                    'reference_number' => $referenceNumber
                ];
                $result = $this->checkSellerTransaction($transdata);
                if ($result['flag']) {
                    $this->setSellerData(
                        $data['payer_status'],
                        $sellerId
                    );
                    $sellerTransaction = $this->membershipModel->load($result['entityId']);
                    $this->emailHelper->sendTransactionEmailToSeller(
                        $this->prepareEmailVariables($sellerTransaction, $feeAmount),
                        $this->getSenderInfo(),
                        $this->getReceiverInfo($sellerTransaction)
                    );
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller_execute Exception : ".$e->getMessage()
            );
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
                "Controller_Index_Ipnnotifyseller_getSenderInfo Exception : ".$e->getMessage()
            );
        }
        return $senderInfo;
    }

    /**
     * getReceiverInfo
     *
     * @param object $sellerTransaction
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
                "Controller_Index_Ipnnotifyseller_getReceiverInfo Exception : ".$e->getMessage()
            );
        }
        return $receiverInfo;
    }

    /**
     * prepareEmailVariables
     *
     * @param object $sellerTransaction
     * @param mixed $amount
     * @return array
     */
    private function prepareEmailVariables($sellerTransaction, $amount)
    {
        $emailTempVariables = [];
        try {
            $templateData = [];
            $transactionHtml = "";

            $timeAndProducts = Transaction::TIME_AND_PRODUCTS;
            $time = Transaction::TIME;
            $products = Transaction::PRODUCTS;

            if ($sellerTransaction->getCheckType() == $timeAndProducts) {
                $templateData['allowed_products'] = $sellerTransaction->getNoOfProducts();
                $templateData['expire_time'] = $sellerTransaction->getExpiryDate();
            } elseif ($sellerTransaction->getCheckType() == $time) {
                $templateData['expire_time'] = $sellerTransaction->getExpiryDate();
            } elseif ($sellerTransaction->getCheckType() == $products) {
                $templateData['allowed_products'] = $sellerTransaction->getNoOfProducts();
            }

            $emailTempVariables['transaction_id'] = $sellerTransaction->getTransactionId();
            $emailTempVariables['fee_amount'] = $amount;
            if (isset($templateData['allowed_products'])) {
                $transactionHtml .= "<tr>
                                <td class='query-details'>
                                    <h3><b>".__('Allowed Products')."</b></h3>
                                    <p>".$templateData['allowed_products']."</p>
                                </td>
                            </tr>";
            }
            if (isset($templateData['expire_time'])) {
                $transactionHtml .= "<tr>
                                <td class='query-details'>
                                    <h3><b>".__('Expires on')."</b></h3>
                                    <p>".$templateData['expire_time']."</p>
                                </td>
                            </tr>";
            }

            $emailTempVariables['myvarcustom'] = $transactionHtml;
            $userdata = $this->helper->getCustomerById($sellerTransaction->getSellerId());
            $username = $userdata->getFirstname()." ".$userdata->getLastname();
            $emailTempVariables['seller_name'] = $username;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller_prepareEmailVariables Exception : ".$e->getMessage()
            );
        }
        return $emailTempVariables;
    }

    /**
     * setSellerData
     *
     * @param string $trStatus
     * @param int $sellerId
     * @return void
     */
    private function setSellerData($trStatus, $sellerId)
    {
        try {
            if ($trStatus == 'verified') {
                $sellerData = $this->sellerCollectionFactory->create();
                $partnerData = $sellerData->addFieldToFilter(
                    'seller_id',
                    ['eq' => $sellerId]
                );

                if ($partnerData->getSize() > 0) {
                    foreach ($partnerData as $seller) {
                        $seller->setIsSeller(1);
                        $seller->save();
                    }
                } else {
                    $seller = [
                        'is_seller' => 1,
                        'seller_id' => $sellerId
                    ];
                    $sellerCollection = $this->sellerModel
                        ->setData($seller)
                        ->save();
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller_setSellerData Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * updateSellerTransaction
     *
     * @param array $data
     * @param int $transactionId
     * @return int
     */
    private function updateSellerTransaction($data, $transactionId = 0)
    {
        $updatedTransactionId = $transactionId;
        try {
            if ($transactionId) {
                $sellerTransaction = $this->membershipModel
                    ->load($transactionId)
                    ->addData($data)
                    ->save();
            } else {
                $sellerTransaction = $this->membershipModel
                    ->setData($data)
                    ->save();
            }
            if ($sellerTransaction->getTransactionStatus()=="pending") {
                $this->helper->updateTransactionType($sellerTransaction->getId(), Transaction::PENDING);
            } elseif ($sellerTransaction->getCheckType() == Transaction::TIME_AND_PRODUCTS) {
                $this->helper->validateTransactionForTimeAndProducts($sellerTransaction);
            } elseif ($sellerTransaction->getCheckType() == Transaction::TIME) {
                $this->helper->validateTransactionForTime($sellerTransaction);
            } elseif ($sellerTransaction->getCheckType() == Transaction::PRODUCTS) {
                $this->helper->validateTransactionForProducts($sellerTransaction);
            } else {
                $this->helper->updateTransactionType($sellerTransaction->getId(), Transaction::EXPIRED);
            }
            $updatedTransactionId = $sellerTransaction->getId();
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller_updateSellerTransaction Exception : ".$e->getMessage()
            );
        }
        return $updatedTransactionId;
    }

    /**
     * checkSellerTransaction
     *
     * @param array $data
     * @return array
     */
    private function checkSellerTransaction($data)
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
                        $entityId = $this->updateSellerTransaction($data, $transactionData->getId());
                        $flag = true;
                    }
                }
            } else {
                $entityId = $this->updateSellerTransaction($data);
                $flag = true;
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Controller_Index_Ipnnotifyseller_checkSellerTransaction Exception : ".$e->getMessage()
            );
        }
        $returnArr['flag'] = $flag;
        $returnArr['entityId'] = $entityId;
        return $returnArr;
    }
}
