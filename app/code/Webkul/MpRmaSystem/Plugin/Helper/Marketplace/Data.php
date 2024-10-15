<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpRmaSystem\Plugin\Helper\Marketplace;

class Data
{
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    protected $authSession;

    /**
     * @param \Magento\Backend\Model\Auth\Session $authSession
     */
    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession
    ) {
        $this->authSession = $authSession;
    }

    /**
     * Initialize Dependencies
     *
     * @param \Webkul\Marketplace\Helper\Data $mpHelper
     * @param array $result
     * @return $result
     * @return void
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $mpHelper,
        $result
    ) {
        $result['mprmasystem/rma/change'] = 'mprmasystem/seller/rma';
        $result['mprmasystem/rma/refund'] = 'mprmasystem/seller/rma';

        return $result;
    }
}
