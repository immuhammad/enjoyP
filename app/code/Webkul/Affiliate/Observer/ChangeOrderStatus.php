<?php
/**
 * Webkul Affiliate Change Order Status Observer.
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Affiliate\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Affiliate\Model\SaleFactory;
use Webkul\Affiliate\Model\UserBalanceFactory;

/**
 * Webkul Affiliate CheckoutSubmitAllAfter Observer Model.
 */
class ChangeOrderStatus implements ObserverInterface
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Webkul\Affiliate\Model\SaleFactory
     */
    private $saleFactory;

    /**
     * @var \Webkul\Affiliate\Model\UserFactory
     */
    private $userFactory;

    /**
     * @var \Webkul\Affiliate\Helper\Email
     */
    private $helperEmail;

    /**
     * @var \Webkul\Affiliate\Logger\Logger
     */
    private $logger;

    /**
     * @param \Magento\Framework\Event\Manager            $eventManager
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Customer\Model\Session             $customerSession
     * @param SessionManager                              $coreSession
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        SaleFactory $saleFactory,
        UserBalanceFactory $userBalance,
        \Webkul\Affiliate\Logger\Logger $logger
    ) {
        $this->saleFactory = $saleFactory;
        $this->logger = $logger;
        $this->userBalance = $userBalance;
    }

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            $order = $observer->getOrder();
            $collection = $this->saleFactory->create()->getCollection()
            ->addFieldToFilter("order_increment_id", $order->getIncrementId());
            foreach ($collection as $model) {
                    $model->setOrderStatus($order->getStatus());
                    $this->_save($model);
            }
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Save Object
     *
     * @param $object
     * @return void
     */
    private function _save($object)
    {
        $object->save();
    }
}
