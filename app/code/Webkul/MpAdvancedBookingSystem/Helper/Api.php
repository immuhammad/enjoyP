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

class Api extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * GetRequiredAttributesForAppointmentBooking
     *
     * @param bool $flag
     */
    public function getRequiredAttributesForAppointmentBooking($flag)
    {
        if ($flag) {
            return [
                'slot_duration',
                'break_time_bw_slot',
                'prevent_scheduling_before',
                'available_every_week',
                'booking_available_from',
                'booking_available_to',
                'slot_for_all_days',
                'slot_has_quantity',
                'location',
                'show_map_loction',
                'show_contact_button_to'
            ];
        }
        return [
            'slot_duration',
            'break_time_bw_slot',
            'prevent_scheduling_before',
            'available_every_week',
            'slot_for_all_days',
            'slot_has_quantity',
            'location',
            'show_map_loction',
            'show_contact_button_to'
        ];
    }

    /**
     * GetRequiredAttributesForTableBooking
     *
     * @param bool $flag
     */
    public function getRequiredAttributesForTableBooking($flag)
    {
        if ($flag) {
            return [
                'hotel_address',
                'hotel_country',
                'hotel_state',
                'location',
                'show_map_loction',
                'show_contact_button_to',
                'price_charged_per_table',
                'max_capacity',
                'slot_duration',
                'break_time_bw_slot',
                'prevent_scheduling_before',
                'slot_for_all_days',
            ];
        }
        return [
            'hotel_address',
            'hotel_country',
            'hotel_state',
            'location',
            'show_map_loction',
            'show_contact_button_to',
            'price_charged_per_table',
            'slot_duration',
            'break_time_bw_slot',
            'prevent_scheduling_before',
            'slot_for_all_days',
        ];
    }

    /**
     * GetRequiredAttributesForHotelBooking
     */
    public function getRequiredAttributesForHotelBooking()
    {
        return [
            'hotel_address',
            'hotel_country',
            'hotel_state',
            'location',
            'show_map_loction',
            'show_contact_button_to',
            'show_nearby_map',
            'price_charged_per_hotel',
            'ask_a_ques_enable',
            'check_in_time',
            'check_out_time'
        ];
    }

    /**
     * GetRentalBookingRequiredAttributes
     *
     * @param int $rentingType
     */
    public function getRentalBookingRequiredAttributes($rentingType)
    {
        // still need to add hourly price and daily price on all
        // Renting Type (Hourly + Daily Basis)
        if ($rentingType == 2) {
            $attributes = [
                'location',
                'show_map_loction',
                'show_contact_button_to',
                'renting_type',
                'available_every_week',
                'prevent_scheduling_before',
                'slot_for_all_days',
                'slot_has_quantity'
            ];
        } elseif ($rentingType == 1) { // Renting Type (Daily Basis)
            $attributes = [
                'location',
                'show_map_loction',
                'show_contact_button_to',
                'renting_type',
                'available_every_week'
            ];
        } else { // Renting Type (Hourly Basis)
            $attributes = [
                'location',
                'show_map_loction',
                'show_contact_button_to',
                'renting_type',
                'available_every_week',
                'prevent_scheduling_before',
                'slot_has_quantity'
            ];
        }
        return $attributes;
    }

    /**
     * GetEventBookingRequiredAttributes
     *
     * @param bool $flag
     */
    public function getEventBookingRequiredAttributes($flag)
    {
        $attributes = [
            'location',
            'show_map_loction',
            'show_contact_button_to',
            'event_date_from',
            'event_date_to',
            'event_chart_available',
            'price_charged_per',
            'is_multiple_tickets'
        ];
        if ($flag) {
            $attributes[] = 'event_chart_image';
        }
        return $attributes;
    }

    /**
     * GetRequiredParamsForDefaultBooking
     *
     * @param bool $flag
     */
    public function getRequiredParamsForDefaultBooking($flag)
    {
        if ($flag == 1) {
            $attributes = [
                'start_date',
                'end_date',
                'time_slot',
                'break_time'
            ];
        } elseif ($flag == 2) {
            $attributes = [
                'start_date',
                'end_date'
            ];
        }
        return $attributes;
    }
}
