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

namespace Webkul\SellerSubDomain\Plugin\Action;

use Magento\Framework\App\Action\Context;

class Action
{
    /**
     * @var \Webkul\SellerSubDomain\Helper\Data
     */
    protected $_helper;

    /**
     * @var \Magento\Framework\App\ResponseInterface
     */
    protected $_response;

    /**
     * @param Context                             $context
     * @param \Webkul\SellerSubDomain\Helper\Data $data
     */
    public function __construct(
        Context $context,
        \Webkul\SellerSubDomain\Helper\Data $data
    ) {
        $this->_helper = $data;
        $this->_response = $context->getResponse();
    }

    /**
     * Redirect to main domain
     *
     * @param  $request
     * @return void
     */
    public function beforeDispatch(\Magento\Framework\App\Action\Action $dispatch, $request)
    {
        if ($this->_helper->isModuleEnable() && !$this->_helper->isAllPagesAllowedOnSellerDomain()) {
            if ($this->_helper->getAllowedUrl() != "") {
                $this->_response->setRedirect($this->_helper->getAllowedUrl());
                $this->_response->sendResponse();
            }
        }
    }
}
