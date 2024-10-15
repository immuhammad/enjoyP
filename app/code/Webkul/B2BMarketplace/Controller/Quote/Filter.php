<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_B2BMarketplace
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\B2BMarketplace\Controller\Quote;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;

class Filter extends \Magento\Customer\Controller\AbstractAccount
{
    public function __construct(
        Context $context,
        \Webkul\B2BMarketplace\Helper\Quote $b2bQuoteHelper
    ) {
        parent::__construct(
            $context
        );
        $this->_b2bQuoteHelper = $b2bQuoteHelper;
    }

    public function execute()
    {
        $this->_b2bQuoteHelper->setQuoteFilters();
        $this->redirect = $this->_objectManager->create(\Magento\Framework\App\Response\RedirectInterface::class);
        $redirectUrl = $this->redirect->getRedirectUrl();

        return $this->resultRedirectFactory->create()->setUrl($redirectUrl);
    }
}
