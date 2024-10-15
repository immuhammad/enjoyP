<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpRmaSystem
 * @author    Webkul Software Private Limited
 * @copyright Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpRmaSystem\Plugin\Model\Customer;

class Session
{
    /**
     * @var \Webkul\MpRmaSystem\Helper\Data
     */
    protected $mpHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * Initialize Dependencies
     *
     * @param \Webkul\MpRmaSystem\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @return void
     */
    public function __construct(
        \Webkul\MpRmaSystem\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder
    ) {
        $this->helper = $helper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Insert title and number for concrete document type.
     *
     * @param \Magento\Customer\Model\Session $session
     * @param string $url
     * @return string
     */
    public function beforeAuthenticate(
        \Magento\Customer\Model\Session $session,
        $url = null
    ) {
        if ($this->helper->getIsSeparatePanel()) {
            $currentUrl = $this->urlBuilder->getCurrentUrl();
            if (strpos($currentUrl, 'mprmasystem/seller') !== false) {
                $url = $this->urlBuilder->getUrl("marketplace/account/login");
            }
        }

        return [$url];
    }
}
