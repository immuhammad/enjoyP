<?php

namespace Webkul\SellerSubDomain\Plugin\Catalog\Model;

use \Magento\Framework\App\Helper\Context;

class Layer
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

    public function afterGetProductCollection(\Magento\Catalog\Model\Layer $subject, $collection)
    {
        $seller = $this->_helper->getProfileDetail();
        if ($seller) {
            $allowedProList = $this->_helper->getProductIds();
            $collection->addAttributeToFilter('entity_id', ['in' => $allowedProList]);
        }
        return $collection;
    }
}
