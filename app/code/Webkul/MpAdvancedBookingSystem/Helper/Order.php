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
namespace Webkul\MpAdvancedBookingSystem\Helper;

class Order extends Data
{
    /**
     * SaveBookingInfoData
     *
     * @param array $bookingData
     * @param array $bookingInfo
     * @return void
     */
    public function saveBookingInfoData($bookingData, $bookingInfo)
    {
        try {
            $slotId = $bookingData['slot_id'];
            $parentId = $bookingData['parent_slot_id'];
            $slotDayIndex = $bookingData['slot_day_index'];
            $qty = $bookingData['qty'];
            $productId = $bookingData['product_id'];
            $attributeSetId = $bookingData['attribute_set_id'];
            $appointmentAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Appointment Booking'
            );
            $eventAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Event Booking'
            );
            $rentalAttrSetId = $this->getProductAttributeSetIdByLabel(
                'Rental Booking'
            );
            $bookingInfoData = $this->getJsonDecodedString($bookingInfo['info']);
            $totalSlots = 0;
            $updateData = false;
            if ($attributeSetId == $eventAttrSetId && !empty($bookingInfoData[0])) {
                $updateData = true;
                $data = $this->getEventTotalSlots(
                    $bookingInfo,
                    $bookingInfoData,
                    $parentId,
                    $slotId,
                    $productId,
                    $qty
                );
                $totalSlots = $data['total_slots'];
                $bookingInfoData = $data['booking_info'];
            } elseif ($attributeSetId == $appointmentAttrSetId) {
                $updateData = true;
                $data = $this->getAppointmentTotalSlots(
                    $bookingInfo,
                    $bookingInfoData,
                    $slotDayIndex,
                    $parentId,
                    $slotId,
                    $qty,
                    $productId
                );
                $totalSlots = $data['total_slots'];
                $bookingInfoData = $data['booking_info'];
            } elseif ($attributeSetId == $rentalAttrSetId) {
                $updateData = true;
                $data = $this->getRentalTotalSlots(
                    $bookingInfo,
                    $bookingInfoData,
                    $slotDayIndex,
                    $parentId,
                    $slotId,
                    $qty,
                    $productId
                );
                $totalSlots = $data['total_slots'];
                $bookingInfoData = $data['booking_info'];
            }
            $infoData = [
                'info' => $this->getJsonEcodedString($bookingInfoData),
                'total_slots' => $totalSlots
            ];
            if ($updateData) {
                $this->updateBookingInfo($bookingInfo['id'], $infoData);
            }
        } catch (\Exception $e) {
            $this->logDataInLogger("Helper_Order_saveBookingInfoData Exception : ".$e->getMessage());

        }
    }

    /**
     * GetRentalTotalSlots
     *
     * @param array $bookingInfo
     * @param array $bookingInfoData
     * @param int $slotDayIndex
     * @param int $parentId
     * @param int $slotId
     * @param int $qty
     * @param int $productId
     * @return array
     */
    private function getRentalTotalSlots(
        $bookingInfo,
        $bookingInfoData,
        $slotDayIndex,
        $parentId,
        $slotId,
        $qty,
        $productId
    ) {
        $totalSlots = 0;
        if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
            $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
            $totalSlots = $bookingInfo['total_slots'] + $qty;
            $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty + $qty;
            $this->setInStock($productId, $totalSlots);
            $this->updateProduct($productId);
        } else {
            $totalSlots = $bookingInfo['total_slots'] + $qty;
            $this->setInStock($productId, $totalSlots);
            $this->updateProduct($productId);
        }
        return [
            'total_slots' => $totalSlots,
            'booking_info' => $bookingInfoData
        ];
    }

    /**
     * GetAppointmentTotalSlots
     *
     * @param array $bookingInfo
     * @param array $bookingInfoData
     * @param int $slotDayIndex
     * @param int $parentId
     * @param int $slotId
     * @param int $qty
     * @param int $productId
     * @return array
     */
    private function getAppointmentTotalSlots(
        $bookingInfo,
        $bookingInfoData,
        $slotDayIndex,
        $parentId,
        $slotId,
        $qty,
        $productId
    ) {
        $totalSlots = 0;
        if (!empty($bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId])) {
            $slotAvailableQty = $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'];
            $totalSlots = $bookingInfo['total_slots'] + $qty;
            $bookingInfoData[$slotDayIndex][$parentId]['slots_info'][$slotId]['qty'] = $slotAvailableQty + $qty;
            $this->setInStock($productId, $totalSlots);
            $this->updateProduct($productId);
        }
        return [
            'total_slots' => $totalSlots,
            'booking_info' => $bookingInfoData
        ];
    }

    /**
     * GetEventTotalSlots
     *
     * @param array $bookingInfo
     * @param array $bookingInfoData
     * @param int $parentId
     * @param int $slotId
     * @param int $productId
     * @param int $qty
     * @return array
     */
    private function getEventTotalSlots(
        $bookingInfo,
        $bookingInfoData,
        $parentId,
        $slotId,
        $productId,
        $qty
    ) {
        $bookingOption = $bookingInfoData[0];
        $totalSlots = 0;
        if (!empty($bookingOption['option_id']) && !empty($bookingOption['values'])) {
            if ($bookingOption['option_id'] == $parentId) {
                $slotAvailableQty = 0;
                $bookingOptionValues = $bookingOption['values'];
                foreach ($bookingOptionValues as $key => $value) {
                    if ($value['option_type_id'] == $slotId) {
                        $slotAvailableQty = $value['qty'];
                        $bookingInfoData[0]['values'][$key]['qty'] = $slotAvailableQty + $qty;
                    }
                }
                $totalSlots = $bookingInfo['total_slots'] + $qty;
                $product = $this->getProduct($productId);
                $this->createOption($bookingInfoData, $product);
                $this->setInStock($productId, $totalSlots);
                $this->updateProduct($productId);
            }
        }
        return [
            'total_slots' => $totalSlots,
            'booking_info' => $bookingInfoData
        ];
    }
}
