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
namespace Webkul\B2BMarketplace\Plugin\Customer\Model\Account;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\ResultFactory;

class Redirect
{
    /**
     * @var \Webkul\B2BMarketplace\Helper\Data
     */
    private $_helper;

    public function __construct(
        \Webkul\B2BMarketplace\Helper\Data $helper,
        ResultFactory $resultFactory
    ) {
        $this->_helper = $helper;
        $this->_resultFactory = $resultFactory;
    }

    public function afterGetRedirect(
        \Magento\Customer\Model\Account\Redirect $subject,
        $result
    ) {
        if ($this->_helper->isSupplierLoginRequest()) {
            $result = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl($this->_helper->getSupplierLoginUrl());
        }

        return $result;
    }
}
