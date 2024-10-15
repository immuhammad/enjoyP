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

namespace Webkul\Mpmembership\Controller\Adminhtml;

use Magento\Backend\App\Action;

/**
 * Webkul Mpmembership Admin Producttransaction Controller
 */
abstract class Producttransaction extends \Magento\Backend\App\Action
{

    /**
     * Check for is allowed.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Webkul_Mpmembership::producttransaction');
    }
}
