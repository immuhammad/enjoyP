<?php
/**
 * Webkul Affiliate Account Navigation.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

/**
 * Affiliate Navigation link
 */
class Navigation extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * getNavSelectClass for select navgation link if current page url match
     * @param string $navUrl
     * @return string
     */
    public function getNavSelectClass($navUrl)
    {
        return strpos($this->_urlBuilder->getCurrentUrl(), $navUrl) ? 'current' : '';
    }

    /**
     * getNavUrl for get url for navigation links
     * @param string $navUrl
     * @return string
     */
    public function getNavUrl($navUrl)
    {
        return $this->getUrl($navUrl, ['_secure' => $this->getRequest()->isSecure()]);
    }
}
