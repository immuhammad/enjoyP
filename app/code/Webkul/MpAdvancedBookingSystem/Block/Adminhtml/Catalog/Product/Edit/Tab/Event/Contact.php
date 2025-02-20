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
namespace Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Event;

class Contact extends \Webkul\MpAdvancedBookingSystem\Block\Adminhtml\Catalog\Product\Edit\Tab\Booking
{
    /**
     * @var string
     */
    protected $_template = 'Webkul_MpAdvancedBookingSystem::product/edit/event/contact.phtml';

    /**
     * Accordion block id
     *
     * @var string
     */
    protected $_blockId = 'bookingContactInfo';
}
