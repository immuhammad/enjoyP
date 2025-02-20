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
namespace Webkul\MpAdvancedBookingSystem\Controller\Booking;

use Magento\Framework\App\Action\Context;

class Slots extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Webkul\MpAdvancedBookingSystem\Helper\Data
     */
    protected $_bookingHelper;

    /**
     * @var \Webkul\MpAdvancedBookingSystem\Model\SlotFactory
     */
    protected $_slot;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJson;

    /**
     * @param Context $context
     * @param \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper
     * @param \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJson
     */
    public function __construct(
        Context $context,
        \Webkul\MpAdvancedBookingSystem\Helper\Data $bookingHelper,
        \Webkul\MpAdvancedBookingSystem\Model\SlotFactory $slot,
        \Magento\Framework\Controller\Result\JsonFactory $resultJson
    ) {
        $this->_bookingHelper = $bookingHelper;
        $this->_slot = $slot;
        $this->_resultJson = $resultJson;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $data = $this->getRequest()->getParams();
            if (!empty($data['product_id']) && !empty($data['date'])) {
                $productId = $data['product_id'];
                $date = $data['date'];
                $helper = $this->_bookingHelper;
                $slotModel = $this->_slot->create();
                $slots = $helper->getSlots($productId);
                if (array_key_exists($date, $slots)) {
                    $info = $slots[$date];
                } else {
                    $info = [];
                }
                $date = date("d-m-Y", strtotime($date));
                $slots = [];
                if (count($info) > 0) {
                    $parentId = $helper->getParentSlotId($productId);
                    $bookedSlots = $helper->getBookedSlotsQty($parentId);
                    foreach ($info as $key => $item) {
                        $currentTime = $helper->getCurrentTime();
                        $currentDate = strToTime($helper->getCurrentDate());
                        $startTime = strtotime($helper->convertTimeFromSeconds($item['startTime']));
                        $selectedDate = strtotime($date);
                        if ($currentDate == $selectedDate && $startTime <= $currentTime) {
                            $doNothing = true;
                        } else {
                            $item['date'] = $date;
                            $slots[] = $helper->formatSlot($item, $bookedSlots);
                        }
                    }

                    if (!empty($slots)) {
                        $result = ['avl' => 1, 'msg' => "success", 'slots' => $slots, 'parent_id' => $parentId];
                    } else {
                        $result = ['avl' => 0, 'msg' => __("No slot available")];
                    }
                } else {
                    $result = ['avl' => 0, 'msg' => __("No slot available")];
                }
            } else {
                $result = ['avl' => 0, 'msg' => __("No slot available")];
            }
            return $this->_resultJson->create()->setData($result);
        } catch (\Exception $e) {
            $this->_bookingHelper->logDataInLogger("Controller_Booking_Slots execute : ".$e->getMessage());
        }
    }
}
