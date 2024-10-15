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
namespace Webkul\B2BMarketplace\Plugin\Customer\Model\Customer;

class DataProvider
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

    public function afterGetData(
        \Magento\Customer\Model\Customer\DataProvider $subject,
        $result
    ) {
        try {
            $supplierAddress = $this->_helper->getSupplierAddressIds();
            foreach ($result as $k => $item) {
                foreach ($result[$k]['address'] as $key => $address) {
                    if (in_array($address["entity_id"], $supplierAddress)) {
                        unset($result[$k]['address'][$key]);
                    }
                }
            }
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        return $result;
    }
}
