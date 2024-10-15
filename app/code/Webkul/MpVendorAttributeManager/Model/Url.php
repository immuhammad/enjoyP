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
namespace Webkul\MpVendorAttributeManager\Model;

use Magento\Framework\UrlInterface;

/**
 * Customer url model
 */
class Url
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * Constructor
     *
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Retrieve customer register form url
     *
     * @return string
     */
    public function getVendorUrl()
    {
        return $this->urlBuilder->getUrl('customer/account/create', ['v' => 1]);
    }
}
