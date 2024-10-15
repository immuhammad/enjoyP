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
namespace Webkul\B2BMarketplace\Plugin\Customer\Block\Account;

class ForgotPassword
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

    public function afterGetUrl(
        \Magento\Customer\Block\Account\Forgotpassword $subject,
        $result
    ) {;
        $post = $this->request->getParams();
        if (isset($post['module']) && ($post['module'] == 'webkul_b2bmarketplace')) {
            return $result."module/webkul_b2bmarketplace";
        }
        return $result;
    }
}
