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
namespace Webkul\MpAdvancedBookingSystem\Api;

interface BookingSystemInterface
{
    /**
     * Get Bookings List
     *
     * @api
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return Magento\Framework\Api\SearchResults
     */
    public function getBookingsList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
