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
namespace Webkul\B2BMarketplace\Plugin\Marketplace\Helper;

class Data
{
    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    private $_helper;

    public function __construct(
        \Webkul\B2BMarketplace\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    public function afterGetSellerPolicyApproval(
        \Webkul\Marketplace\Helper\Data $subject,
        $result
    ) {
        return true;
    }

    public function afterGetIsSeparatePanel(
        \Webkul\Marketplace\Helper\Data $subject,
        $result
    ) {
        return true;
    }

    /**
     * afterGetControllerMappedPermissions.
     *
     * @param \Webkul\Marketplace\Helper\Data $helperData
     * @param array $result
     *
     * @return array
     */
    public function afterGetControllerMappedPermissions(
        \Webkul\Marketplace\Helper\Data $helperData,
        $result
    ) {
        $result['b2bmarketplace/supplier/settings'] = 'marketplace/account/editprofile';
        $result['b2bmarketplace/supplier/info_update'] = 'marketplace/account/editprofile';
        $result['b2bmarketplace/supplier/fetch'] = 'b2bmarketplace/supplier/message';
        $result['b2bmarketplace/supplier/messagePost'] = 'b2bmarketplace/supplier/message';
        $result['b2bmarketplace/supplier/verification_verify'] = 'b2bmarketplace/supplier/verification';
        $result['b2bmarketplace/supplier/verification_send'] = 'b2bmarketplace/supplier/verification';
        $result['b2bmarketplace/supplier/quote_requestPost'] = 'b2bmarketplace/supplier/quote_request';
        $result['b2bmarketplace/supplier_quote_product/fetch'] = 'b2bmarketplace/supplier/quotes';
        return $result;
    }

    public function afterGetSellerRegistrationUrl(
        \Webkul\Marketplace\Helper\Data $subject,
        $result
    ) {
        return $this->_helper->getSupplierRegistrationUrl();
    }

    /**
     * Check whether seller profile is complete or not
     *
     * @return boolean
     */
    public function afterIsProfileCompleted(
        \Webkul\Marketplace\Helper\Data $subject,
        $result
    ) {
        $sellerData = $subject->getSeller();
        $fields = ["twitter_id", "facebook_id", "banner_pic", "logo_pic", "company_description"];
        try {
            foreach ($fields as $field) {
                if ($sellerData[$field] == "") {
                    return false;
                }
            }
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }
}
