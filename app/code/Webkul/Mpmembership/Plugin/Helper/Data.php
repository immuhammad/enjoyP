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
namespace Webkul\Mpmembership\Plugin\Helper;

use Webkul\Mpmembership\Model\Config\Source\Feeapplied;

class Data
{
    /**
     * @param \Webkul\Mpmembership\Helper\Data $helper
     */
    public function __construct(
        \Webkul\Mpmembership\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * function to run to change the return data of GetControllerMappedPermissions.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['mpmembership/index/index'] = 'mpmembership/index/index';
        $result['mpmembership/index/paypalsellerreturn'] = 'mpmembership/index/index';
        $result['mpmembership/index/paypalreturn'] = 'mpmembership/index/index';
        // $result['mpmembership/index/ipnnotifyseller'] = 'mpmembership/index/index';
        // $result['mpmembership/index/ipnnotifyproduct'] = 'mpmembership/index/index';
        return $result;
    }

    /**
     * function to change the return data of getIsProductApproval.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return bool
     */
    public function afterGetIsProductApproval(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $feeAppliedFor = $this->helper->getConfigFeeAppliedFor();
        if ($this->helper->isModuleEnabled()
            && $feeAppliedFor == Feeapplied::PER_PRODUCT
        ) {
            return true;
        }
        return $result;
    }
}
