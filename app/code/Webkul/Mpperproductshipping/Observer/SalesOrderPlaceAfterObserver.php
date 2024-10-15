<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Mpperproductshipping
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Mpperproductshipping\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    /**
     * @var ordersCollection
     */
    protected $ordersCollection;

    /**
     * @var Session
     */
    protected $_session;

    /**
     * @param SessionManager                              $session
     */
    public function __construct(
        \Webkul\Marketplace\Model\OrdersFactory $ordersCollection,
        SessionManager $session
    ) {
        $this->ordersCollection = $ordersCollection;
        $this->_session = $session;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var $orderInstance Order */
        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $shippingmethod = $order->getShippingMethod();

        if (strpos($shippingmethod, 'webkulmpperproduct') !== false) {
            $shippingAll = $this->_session->getShippingInfo();
            foreach ($shippingAll['webkulmpperproduct'] as $shipdata) {
                $collection = $this->ordersCollection->create()
                                ->getCollection()
                                ->addFieldToFilter('order_id', ['eq' => $lastOrderId])
                                ->addFieldToFilter('seller_id', ['eq' => $shipdata['seller_id']])
                                ->getFirstItem();
                if ($collection->getEntityId()) {
                    $collection->setCarrierName($shipdata['submethod'][0]['method']);
                    $collection->setShippingCharges($shipdata['submethod'][0]['cost']);
                    $collection->save();
                }
            }
            $this->_session->unsetShippingInfo();
        }
    }
}
