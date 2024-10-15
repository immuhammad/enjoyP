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

use Webkul\Mpmembership\Model\ResourceModel\Product\CollectionFactory;

/**
 * Webkul Mpmembership Transactionlist Create Block for product transaction
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
    protected $productList;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

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
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Webkul\Mpmembership\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * getAllTransactions get all transactions done to enable products
     *
     * @return object [return collection]
     */
    public function getAllTransactions()
    {
        try {
            if (!$this->productList) {
                $data = $this->getRequest()->getParams();

                $filteredData = $this->getFilteredData($data);

                $transactionModel = $this->collectionFactory->create();
                $userId = $this->helper->getSellerId();

                $collection = $transactionModel
                    ->addFieldToFilter(
                        'seller_id',
                        ['eq'  => $userId]
                    )->addFieldToFilter(
                        'transaction_id',
                        ['like'  => '%'.$filteredData['filterTrId'].'%']
                    )->addFieldToFilter(
                        'transaction_email',
                        ['like'  => '%'.$filteredData['filterTrEmail'].'%']
                    )->addFieldToFilter(
                        'transaction_status',
                        ['like'  => '%'.$filteredData['filterTrStatus'].'%']
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
                    $this->productList = $collection;
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Transactionlist_getAllTransactions Exception : ".$e->getMessage()
            );
        }
        return $this->productList;
    }

    /**
     * getFilteredData used to get filtered data
     *
     * @param array $data [contains request data]
     *
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

            if (isset($data['tr_id'])) {
                $filterTrId = $data['tr_id'] != '' ? $data['tr_id'] : '';
            }

            if (isset($data['tr_email'])) {
                $filterTrEmail = $data['tr_email'] != '' ? $data['tr_email'] : '';
            }

            if (isset($data['tr_status'])) {
                $filterTrStatus = $data['tr_status'] != '' ? $data['tr_status'] : '';
            }

            if (isset($data['from_date'])) {
                $filterTrDateFrom = $data['from_date'] != '' ? $data['from_date'] : '';
            }

            if (isset($data['to_date'])) {
                $filterTrDateTo = $data['to_date'] != '' ? $data['to_date'] : '';
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
                'filterTrId'  => $filterTrId,
                'filterTrEmail' => $filterTrEmail,
                'filterTrStatus' => $filterTrStatus,
                'from' => $from,
                'to' => $to,
            ];

            return $wholeData;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Transactionlist_getFilteredData Exception : ".$e->getMessage()
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
                    'mpmembership.producttransaction.list.pager'
                )->setCollection(
                    $this->getAllTransactions()
                );
                $this->setChild('pager', $pager);
                $this->getAllTransactions()->load();
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Transactionlist _prepareLayout Exception : ".$e->getMessage()
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
                "Block_Product_Transactionlist_getPagerHtml Exception : ".$e->getMessage()
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
                "Block_Product_Transactionlist_getIsSecure Exception : ".$e->getMessage()
            );
        }
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
                "Block_Product_Transactionlist_getParameters Exception : ".$e->getMessage()
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
     * getProductData used to get product names with links by product ids
     *
     * @param string $ids [contains product ids as comma seperated]
     *
     * @return string [returns comma seperated product SKUs]
     */
    public function getProductData($ids)
    {
        $html = "";
        try {
            if ($ids) {
                $productIds = explode(",", $ids);
                if (!empty($productIds)) {
                    foreach ($productIds as $id) {
                        $_product = $this->productRepository->getById($id);
                        if ($_product && $_product->getId()) {
                            $html .= "<a style='display:block' href='".$this->getUrl(
                                'marketplace/product/edit',
                                ['id' => $_product->getId()]
                            )."' target='blank' title='".__('View Product')."'>".$_product->getName().'</a>';
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "Block_Product_Transactionlist_getProductData Exception : ".$e->getMessage()
            );
        }
        return $html;
    }
}
