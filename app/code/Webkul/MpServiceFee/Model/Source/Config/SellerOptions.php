<?php
/**
 * Webkul Service Fee Type Source Model.
 * @category  Webkul
 * @package   Webkul_MpServiceFee
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpServiceFee\Model\Source\Config;

class SellerOptions extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{

    /**
     * Class constructor
     *
     * @param \Webkul\MpServiceFee\Helper\Servicehelper $curHelper
     */
    public function __construct(
        \Webkul\MpServiceFee\Helper\Servicehelper $curHelper
    ) {
        $this->curHelper = $curHelper;
    }
    
    /**
     * Retrieve all options array
     *
     * @return array
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = $this->curHelper->getSellerList();
        }
        return $this->_options;
    }
}
