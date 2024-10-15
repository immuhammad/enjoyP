<?php
/**
 * Webkul Affiliate User Status.
 *
 * @category  Webkul
 * @package   Webkul_Affiliate
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\Affiliate\Block\User;

class Status extends \Webkul\Affiliate\Block\User\UserAbstract
{
    /**
     * getBlogUrlHint for check affiliate user status
     * @return array;
     */
    public function getBlogUrlHint()
    {
        $config = $this->getAffiliateConfig();
        return $config['blog_url_hint'];
    }

    /**
     * isAffilateRegistration
     * @return bool
     */
    public function getAffilateRegistrationTerms()
    {
        return $this->affDataHelper->filterContent($this->_scopeConfig->getValue('affiliate/terms/editor_textarea'));
    }
}
