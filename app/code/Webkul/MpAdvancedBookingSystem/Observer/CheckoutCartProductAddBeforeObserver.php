<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvancedBookingSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpAdvancedBookingSystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckoutCartProductAddBeforeObserver implements ObserverInterface
{

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->_request = $request;
        $this->_bookingHelper = $bookingHelper;
        $this->_checkoutSession = $checkoutSession;
    }

    /**
     * Checkout cart product add before event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_bookingHelper;
        $product = $observer->getProduct();
        $productType = $product->getTypeId();
        
        if ($productType == 'booking' && $product->getId()) {
            $productSetId = $product->getAttributeSetId();
            $allowedAttrSetIDs = $helper->getAllowedAttrSetIDs();
            $eventAttrSetId = $helper->getProductAttributeSetIdByLabel(
                'Event Booking'
            );

            if (in_array($productSetId, $allowedAttrSetIDs) && $productSetId == $eventAttrSetId) {
                $itemQty = 0;
                $item =  $this->_checkoutSession->getQuote()->getItemByProduct($product);
                if ($item && $item->getId()) {
                    $itemQty = $item->getQty();
                }
                $this->checkQtyAvailibilty($product, $itemQty);
            }
        }
    }

    /**
     * CheckQtyAvailibilty
     *
     * @param object $product
     * @param int $itemQty
     */
    public function checkQtyAvailibilty($product, $itemQty)
    {
        $helper = $this->_bookingHelper;
        $data = $this->_request->getParams();
        $eventOptionsData = [];
        $savedOptionId = 0;
        $savedOptionQty = [];
        $eventOptions = $helper->getEventOptions($product);
        
        if (!empty($eventOptions['event_ticket'])) {
            $eventOptionsData = $eventOptions['event_ticket'];
        }
        if (!empty($eventOptionsData['option_id'])) {
            $savedOptionId = $eventOptionsData['option_id'];
        }
        if (!empty($data['options'][$savedOptionId])) {
            if (count($data['options'][$savedOptionId]) === 1) {
                $optionValId = $data['options'][$savedOptionId][0];
                $bookingInfo = $helper->getBookingInfo($product->getId());
                $bookedData = $helper->getBookedEventData(
                    $product->getId(),
                    $bookingInfo,
                    $savedOptionId,
                    $optionValId
                );
                foreach ($eventOptionsData['option_values'] as $value) {
                    if (empty($value['option_type_id'])) {
                        break;
                    }
                    $savedOptionQty[$value['option_type_id']] = $value['qty'];
                    $savedOptionTitle[$value['option_type_id']] = $value['title'];
                }
                if (!empty($savedOptionQty[$optionValId])) {
                    $availableQty = $savedOptionQty[$optionValId];
                } else {
                    $availableQty = 0;
                }
                if (!empty($bookedData[$savedOptionId][$optionValId])) {
                    $bookedQty = $bookedData[$savedOptionId][$optionValId];
                    if ($bookedQty > $availableQty) {
                        $availableQty = 0;
                    } else {
                        $availableQty = $availableQty - $bookedQty;
                    }
                }
                $totalItemQty = $data['qty'] + $itemQty;
                if ($totalItemQty > $availableQty) {
                    if (empty($savedOptionTitle[$optionValId])) {
                        $savedOptionTitle[$optionValId] = '';
                    }
                    $errorMessage = __(
                        'Only %1 quantity is available for %2 ticket "%3".',
                        $availableQty,
                        $product->getName(),
                        $savedOptionTitle[$optionValId]
                    );
                    throw new \Magento\Framework\Exception\LocalizedException(__($errorMessage));
                }
            }
        }
    }
}
