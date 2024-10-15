<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Helper;

class MpHelper extends \Webkul\Marketplace\Helper\Data
{
    public function getRequestQuoteUrl()
    {
        $targetUrl = $this->getTargetUrlPath();
        if ($targetUrl) {
            $temp = explode('/requestQuote/shop', $targetUrl);
            if (!isset($temp[1])) {
                $temp[1] = '';
            }
            $temp = explode('/', $temp[1]);
            if (isset($temp[1]) && $temp[1] != '') {
                $temp1 = explode('?', $temp[1]);

                return $temp1[0];
            }
        }

        return false;
    }

    public function getQuickOrderUrl()
    {
        $targetUrl = $this->getTargetUrlPath();
        if ($targetUrl) {
            $temp = explode('/quickOrder/shop', $targetUrl);
            if (!isset($temp[1])) {
                $temp[1] = '';
            }
            $temp = explode('/', $temp[1]);
            if (isset($temp[1]) && $temp[1] != '') {
                $temp1 = explode('?', $temp[1]);

                return $temp1[0];
            }
        }

        return false;
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    public function isMagentoSwatchesModuleInstalled()
    {
        return $this->_moduleManager->isEnabled('Magento_Swatches');
    }
}
