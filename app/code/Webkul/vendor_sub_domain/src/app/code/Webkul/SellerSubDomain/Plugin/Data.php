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
use Magento\Customer\Model\Session as CustomerSession;

class Data
{
    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_mphelper;

    /**
     * @var Magento\UrlRewrite\Model\UrlRewrite
     */
    protected $_urlRewrite;

    protected $_targetUrl;

    /**
     * @param Context                                     $context
     * @param CustomerSession                             $customerSession
     * @param \Webkul\SellerSubDomain\Helper\Data         $data
     * @param \Webkul\Marketplace\Helper\Data             $mphelper
     * @param \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        \Webkul\SellerSubDomain\Helper\Data $data,
        \Webkul\Marketplace\Helper\Data $mphelper,
        \Magento\UrlRewrite\Model\UrlRewriteFactory $urlRewrite
    ) {
        $this->_customerSession = $customerSession;
        $this->_helper = $data;
        $this->_mphelper = $mphelper;
        $this->_urlRewrite = $urlRewrite;
        $this->_urlBuilder = $context->getUrlBuilder();
    }

    /**
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param $result
     * @return string
     */
    public function beforeGetRewriteUrl(\Webkul\Marketplace\Helper\Data $subject, $targetUrl)
    {
        $this->_targetUrl = $targetUrl;
    }

    /**
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param $result
     * @return string
     */
    public function afterGetRewriteUrl(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        return $this->_helper->getUrl($result, $this->_targetUrl);
    }

    /**
     * @param \Webkul\Marketplace\Helper\Data $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetTargetUrlPath(\Webkul\Marketplace\Helper\Data $subject, callable $proceed)
    {
        $urls = explode(
            $this->getBaseUrlFromCurrentUrl($this->_urlBuilder->getCurrentUrl()),
            $this->_urlBuilder->getCurrentUrl()
        );
        $targetUrl = '';
        $temp = explode('/?', $urls[1]);
        if (!isset($temp[1])) {
            $temp[1] = '';
        }
        if (!$temp[1]) {
            $temp = explode('?', $temp[0]);
        }
        $requestPath = $temp[0];
        $urlColl = $this->_urlRewrite
            ->create()
            ->getCollection()
            ->addFieldToFilter(
                'request_path',
                ['eq' => $requestPath]
            )
            ->addFieldToFilter(
                'store_id',
                ['eq' => $this->_mphelper->getCurrentStoreId()]
            );
        foreach ($urlColl as $value) {
            $targetUrl = $value->getTargetPath();
        }

        return $targetUrl;
    }

    /**
     * get base url from current url
     *
     * @return string
     */
    public function getBaseUrlFromCurrentUrl($currentUrl)
    {
        $urldata = explode('/', $currentUrl);
        $sslurldata = explode('//', $currentUrl);
        return $sslurldata[0].'//'.$urldata[2].'/';
    }
    /**
     * get base url from current url
     *
     * @return string
     */
    public function afterGetCollectionUrl(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        return $this->_helper->getShopNameByCurrentUrl();
    }

    public function afterGetLocationUrl(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        return $this->_helper->getShopNameByCurrentUrl();
    }

    public function afterGetFeedbackUrl(\Webkul\Marketplace\Helper\Data $subject, $result)
    {
        return $this->_helper->getShopNameByCurrentUrl();
    }
}
