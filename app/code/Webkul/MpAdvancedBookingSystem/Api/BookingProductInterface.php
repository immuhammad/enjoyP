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

interface BookingProductInterface
{
    /**
     * Save Booking Product
     *
     * @api
     * @param \Magento\Catalog\Api\Data\ProductInterface $product
     * @param mixed $bookingData
     *
     * @return \Webkul\MpAdvancedBookingSystem\Api\ResponseInterface
     */
    public function saveBookingProduct($product, $bookingData);
}
