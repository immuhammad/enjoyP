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
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul Mpmembership Observer MpApproveProductObserver
 */
class MpApproveProductObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Webkul\Mpmembership\Model\ProductFactory
     */
    protected $membershipModel;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Webkul\Mpmembership\Helper\Email
     */
    protected $emailHelper;

    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param \Webkul\Mpmembership\Model\ProductFactory $membershipModel
     * @param ProductRepositoryInterface $productRepository
     * @param \Webkul\Mpmembership\Helper\Email $emailHelper
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Webkul\Mpmembership\Model\ProductFactory $membershipModel,
        ProductRepositoryInterface $productRepository,
        CollectionFactory $collectionFactory,
        \Webkul\Mpmembership\Helper\Email $emailHelper
    ) {
        $this->helper = $helper;
        $this->date = $date;
        $this->mpHelper = $mpHelper;
        $this->membershipModel = $membershipModel;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->emailHelper = $emailHelper;
    }

    /**
     * Marketplace Product Approved after observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $product = $observer->getEvent()->getProduct();
            $seller = $observer->getEvent()->getSeller();
            $sellerMemberProducts = $this->collectionFactory->create()
                                ->addFieldToFilter('product_ids', ['finset' => [$product->getMageproductId()]]);
            if (!$sellerMemberProducts->getSize()) {
                $feeAmount = 0;
                $transdata = [
                'seller_id' => $seller->getId(),
                'transaction_email' => $this->mpHelper->getAdminEmailId(),
                'no_of_products' => 1,
                'product_ids' => $product->getMageproductId(),
                'transaction_status' => "Approved By Admin",
                'reference_number' => 'N/A'
                ];
                $sellerTransaction = $this->membershipModel->create()
                ->setData($transdata)
                ->save();
                $this->emailHelper->sendTransactionEmailToSeller(
                    $this->prepareEmailVariables($sellerTransaction, $feeAmount, [$product->getMageproductId()]),
                    $this->getSenderInfo(),
                    $this->getReceiverInfo($sellerTransaction)
                );
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger("Observer_MpApproveProductObserver execute : ".$e->getMessage());
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
            $adminStoremail = $this->mpHelper->getAdminEmailId();
            $defaultTransEmailId = $this->mpHelper->getDefaultTransEmailId();
            $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
            $adminUsername = $this->mpHelper->getAdminName();
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Observer_MpApproveProductObserver_getSenderInfo Exception : ".$e->getMessage()
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
                "Observer_MpApproveProductObserver_getReceiverInfo Exception : ".$e->getMessage()
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
                "Observer_MpApproveProductObserver_prepareEmailVariables Exception : ".$e->getMessage()
            );
        }
        return $emailTempVariables;
    }
}
