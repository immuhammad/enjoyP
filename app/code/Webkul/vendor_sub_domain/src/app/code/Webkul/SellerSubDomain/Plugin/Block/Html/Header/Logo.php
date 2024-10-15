<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_SellerSubDomain
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\SellerSubDomain\Plugin\Block\Html\Header;

use \Magento\Framework\App\Helper\Context;

class Logo
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                             $context
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_helper = $data;
    }

    /**
     * @param \Magento\Theme\Block\Html\Header\Logo $subject
     * @param $result
     * @return string
     */
    public function afterIsHomePage(\Magento\Theme\Block\Html\Header\Logo $subject, $result)
    {
        if ($result) {
            return $this->_helper->getCurrentUrl() == $this->_helper->getAdminBaseUrl();
        }
        return $result;
    }
}
