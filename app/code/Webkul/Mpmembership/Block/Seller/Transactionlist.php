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

use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Webkul\Mpmembership\Api\TransactionRepositoryInterface;
use Webkul\Mpmembership\Model\Transaction;

/**
 * Webkul Mpmembership Transactionlist Create Block for seller transactions
 */
class Transactionlist extends \Magento\Framework\View\Element\Template
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Webkul\Mpmembership to return collection
     */
    protected $transactionList;

    /**
     * @var \Webkul\Mpmembership\Api\TransactionRepositoryInterface
     */
    protected $transactionRepository;

    /**
     * @var \Webkul\Mpmembership\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param CollectionFactory $collectionFactory
     * @param TransactionRepositoryInterface $transactionRepository
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data  $mpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $collectionFactory,
        TransactionRepositoryInterface $transactionRepository,
        \Webkul\Mpmembership\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->collectionFactory         = $collectionFactory;
        $this->transactionRepository     = $transactionRepository;
        $this->helper                    = $helper;
        $this->mpHelper                    = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * getAllTransactions  get all transactions done for seller
     *
     * @return object [return collection]
     */
    public function getAllTransactions()
    {
        try {
            if (!$this->transactionList) {
                $data = $this->getRequest()->getParams();

                $filteredData = $this->getFilteredData($data);

                $transactionModel = $this->collectionFactory->create();
                $userId = $this->helper->getSellerId();

                $collection = $transactionModel
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq' => $userId]
                    )->addFieldToFilter(
                        'transaction_id',
                        ['like' => '%'.$filteredData['filterTrId'].'%']
                    )->addFieldToFilter(
                        'transaction_email',
                        ['like' => '%'.$filteredData['filterTrEmail'].'%']
                    )->addFieldToFilter(
                        'transaction_status',
                        ['like' => '%'.$filteredData['filterTrStatus'].'%']
                    )->setOrder(
                        'transaction_date',
                        'DESC'
                    );

                if ($filteredData['from'] && $filteredData['to']) {
                    $collection->getSelect()->where(
                        "transaction_date BETWEEN '"
                        . $filteredData['from']
                        ."' AND '"
                        . $filteredData['to']
                        ."'"
                    );
                }

                if ($collection->getSize()) {
                    $this->transactionList = $collection;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Transactionlist_getAllTransactions Exception : ".$e->getMessage()
            );
        }
        return $this->transactionList;
    }

    /**
     * getFilteredData used to get filtered data
     *
     * @param array $data [contains request data]
     * @return array [returns filtered data]
     */
    public function getFilteredData($data)
    {
        try {
            $filterTrId = '';
            $filterTrEmail = '';
            $filterTrDateFrom = '';
            $filterTrDateTo = '';
            $filterTrStatus = '';
            $from = null;
            $to = null;

            if (isset($data['seller_tr_id'])) {
                $filterTrId = $data['seller_tr_id'] != '' ? $data['seller_tr_id'] : '';
            }

            if (isset($data['seller_tr_email'])) {
                $filterTrEmail = $data['seller_tr_email'] != '' ? $data['seller_tr_email'] : '';
            }

            if (isset($data['seller_tr_status'])) {
                $filterTrStatus = $data['seller_tr_status'] != '' ? $data['seller_tr_status'] : '';
            }

            if (isset($data['seller_from_date'])) {
                $filterTrDateFrom = $data['seller_from_date'] != '' ? $data['seller_from_date'] : '';
            }

            if (isset($data['seller_to_date'])) {
                $filterTrDateTo = $data['seller_to_date'] != '' ? $data['seller_to_date'] : '';
            }

            if ($filterTrDateTo) {
                $todate = date_create($filterTrDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }

            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }

            if ($filterTrDateFrom) {
                $fromdate = date_create($filterTrDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $wholeData = [
                'filterTrId' => $filterTrId,
                'filterTrEmail' => $filterTrEmail,
                'filterTrStatus' => $filterTrStatus,
                'from' => $from,
                'to' => $to,
            ];

            return $wholeData;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Transactionlist_getFilteredData Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * _prepareLayout
     *
     * @return object
     */
    protected function _prepareLayout()
    {
        try {
            parent::_prepareLayout();
            if ($this->getAllTransactions()) {
                $pager = $this->getLayout()->createBlock(
                    \Magento\Theme\Block\Html\Pager::class,
                    'mpmembership.sellertransaction.list.pager'
                )->setCollection(
                    $this->getAllTransactions()
                );
                $this->setChild('pager', $pager);
                $this->getAllTransactions()->load();
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Transactionlist _prepareLayout Exception : ".$e->getMessage()
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
                "Block_Seller_Transactionlist_getPagerHtml Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getExpiryDetail used to get transaction is valid or not
     *
     * @param int $id [contains transaction entity id]
     *
     * @return string
     */
    public function getExpiryDetail($id)
    {
        $status = __("expired");
        try {
            $data = $this->transactionRepository->getById($id);
            if ($data->getTransactionType() == Transaction::PENDING) {
                $status = __("pending");
            } elseif ($data->getTransactionType() == Transaction::EXPIRED) {
                $status = __("expired");
            } elseif ($data->getTransactionType() == Transaction::VALID) {
                $status = __("valid");
            } elseif ($data->getTransactionType() == Transaction::TIME_EXPIRED) {
                $status = __("Time Expired");
            } elseif ($data->getTransactionType() == Transaction::PRODUCT_LIMIT_COMPLETED) {
                $status = __("Product Limit Completed");
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Seller_Transactionlist_getExpiryDetail Exception : ".$e->getMessage()
            );
        }
        return $status;
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
                "Block_Seller_Transactionlist_getIsSecure Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getMpHelper
     *
     * @return \Webkul\Marketplace\Helper\Data
     */
    public function getMpHelper()
    {
        return $this->mpHelper;
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
                "Block_Seller_Transactionlist_getParameters Exception : ".$e->getMessage()
            );
        }
    }
}
