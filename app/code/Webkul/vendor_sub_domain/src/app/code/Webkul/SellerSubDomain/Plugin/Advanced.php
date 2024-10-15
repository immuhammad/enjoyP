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

namespace Webkul\SellerSubDomain\Plugin;

use \Magento\Framework\App\Helper\Context;

class Advanced
{
    /**
     *
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @param Context                                     $context
     * @param \Webkul\SellerSubDomain\Helper\Data         $data
     */
    public function __construct(
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_helper = $data;
    }

    public function afterGetProductCollection(\Magento\CatalogSearch\Model\Advanced $subject, $result)
    {
        $seller = $this->_helper->getProfileDetail();
        if ($seller) {
            $allowedProList = $this->_helper->getProductIds();
            $result->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
        return $result;
    }
}
