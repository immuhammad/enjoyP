<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c)   Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\B2BMarketplace\Plugin;

class ForgotPasswordPostAfterRedirect
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;
    
    /**
     * @var \Webkul\Sso\Helper\Data
     */
    private $helper;

    /**
     * __construct function
     *
     * @param \Magento\Framework\App\RequestInterface   $request
     * @param \Magento\Framework\UrlInterface           $urlBuilder
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->request = $request;
        $this->urlBuilder =  $urlBuilder;
    }

    public function afterExecute(
        \Magento\Customer\Controller\Account\ForgotPasswordPost $subject,
        $result
    ) {
        $post = $this->request->getParams();
        if (isset($post['module']) && $post['module'] == 'webkul_b2bmarketplace') {
            $b2bSupplierRedirectUrl = $this->urlBuilder->getUrl("b2bmarketplace/supplier/login/");
            $result->setUrl($b2bSupplierRedirectUrl);
        }
        return $result;
    }
}
