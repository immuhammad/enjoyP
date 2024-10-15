<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpVendorAttributeManager
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpVendorAttributeManager\Plugin\Marketplace\Helper;

class Data
{
    /**
     * @var \Magento\Framework\Url
     */
    protected $_urlBuilder;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Url $urlBuilder
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Url $urlBuilder,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_request = $request;
    }

    /**
     * Function after Get Seller Registration Url
     *
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param [type] $result
     * @return void
     */
    public function afterGetSellerRegistrationUrl(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        $url = $this->_urlBuilder->getUrl(
            'customer/account/create',
            [
                '_secure' => $this->_request->isSecure(),
                'v' => 1
            ]
        );

        return $url;
    }
}
