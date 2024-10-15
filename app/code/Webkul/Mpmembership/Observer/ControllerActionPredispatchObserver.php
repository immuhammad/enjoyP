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
use Webkul\Mpmembership\Model\ResourceModel\Transaction\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

/**
 * Webkul Mpmembership ControllerActionPredispatchObserver Observer.
 */
class ControllerActionPredispatchObserver implements ObserverInterface
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
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $requestInterface;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $mpHelper;

    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\App\RequestInterface $requestInterface
     * @param ManagerInterface $messageManager
     * @param \Magento\Framework\UrlInterface $urlInterface
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper,
        CollectionFactory $collectionFactory,
        \Magento\Framework\App\RequestInterface $requestInterface,
        ManagerInterface $messageManager,
        \Magento\Framework\UrlInterface $urlInterface,
        \Webkul\Marketplace\Helper\Data $mpHelper
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->requestInterface = $requestInterface;
        $this->messageManager = $messageManager;
        $this->urlInterface = $urlInterface;
        $this->mpHelper = $mpHelper;
    }

    /**
     * Controller Action Predispatch observer that checks,
     * if any pack is expired or not and if expired then throws error
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $request = $observer->getEvent()->getData('request');

            $module = $request->getModuleName();
            $controller = $request->getControllerName();
            $action = $request->getActionName();
            $params = $request->getParams();

            if ($this->helper->isModuleEnabled()
                && $module == "marketplace"
                && $controller == "product"
                && $action == "add"
            ) {
                $url = $this->getRedirectUrl($params);

                if (!$this->requestInterface->getParam('id')) {
                    $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();
                    if ($feeAppliedFor == Feeapplied::PER_VENDOR) {
                        $flag = $this->getPermission();
                        if ($flag) {
                            $this->messageManager->addError(
                                __('please pay fee to add products')
                            );
                            $observer->getControllerAction()
                                ->getResponse()
                                ->setRedirect($url);
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "ControllerActionPredispatchObserver_execute Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * getRedirectUrl
     *
     * @param array $params
     * @return string
     */
    private function getRedirectUrl($params)
    {
        try {
            $url = "marketplace/product/create";

            if (isset($params['set']) && isset($params['type'])) {
                $allowedAttributesetIds = $this->mpHelper->getAllowedAttributesetIds();
                $allowedProductType = $this->mpHelper->getAllowedProductType();
                if (trim($allowedAttributesetIds)) {
                    $allowedsets = explode(',', $allowedAttributesetIds);
                }
                if (trim($allowedProductType)) {
                    $allowedtypes = explode(',', $allowedProductType);
                }
                if (count($allowedsets) == 1 && count($allowedtypes) == 1) {
                    $url = "mpmembership";
                }
            }

            return $this->urlInterface->getUrl($url);
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "ControllerActionPredispatchObserver_getRedirectUrl Exception : ".$e->getMessage()
            );
        }
    }

    /**
     * [getPermission checks if any pack is expired or not]
     *
     * @return boolean
     */
    private function getPermission()
    {
        try {
            $customerId = $this->helper->getSellerId();

            $transactionModel = $this->collectionFactory->create();
            $sellerTransactions = $transactionModel
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $customerId]
            );

            $timeAndProducts = \Webkul\Mpmembership\Model\Transaction::TIME_AND_PRODUCTS;
            $time = \Webkul\Mpmembership\Model\Transaction::TIME;
            $products = \Webkul\Mpmembership\Model\Transaction::PRODUCTS;

            $expire = false;

            if ($sellerTransactions->getSize() > 0) {
                foreach ($sellerTransactions as $partner) {
                    if ($partner->getCheckType() == $timeAndProducts) {
                        if ($partner->getExpiryDate() > date('Y-m-d h:i:s')
                            && $partner->getRemainingProducts() < $partner->getNoOfProducts()
                        ) {
                            $expire = false;
                            break;
                        } else {
                            $expire = true;
                        }
                    } elseif ($partner->getCheckType() == $time) {
                        if ($partner->getExpiryDate() > date('Y-m-d h:i:s')) {
                            $expire = false;
                            break;
                        } else {
                            $expire = true;
                        }
                    } elseif ($partner->getCheckType() == $products) {
                        if ($partner->getRemainingProducts() < $partner->getNoOfProducts()) {
                            $expire = false;
                            break;
                        } else {
                            $expire = true;
                        }
                    } else {
                        $expire = true;
                    }
                }
            } else {
                $expire = true;
            }

            return $expire;
        } catch (\Exception $e) {
            $this->helper->logDataInLogger(
                "ControllerActionPredispatchObserver_getPermission Exception : ".$e->getMessage()
            );
            return false;
        }
    }
}
