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

namespace Webkul\B2BMarketplace\Controller\QuickOrder;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\NoSuchEntityException;

class Delete extends Add
{
    /**
     * Delete shopping cart item action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();

        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            if (isset($params['current_url']) && !empty($params['current_url'])) {
                return $params['current_url'];
            } else {
                return $this->resultRedirectFactory->create()->setPath('marketplace/');
            }
        }

        $id = (int)$this->getRequest()->getParam('id');
        if ($id) {
            try {
                $this->cart->removeItem($id)->save();
                $resultData['error'] = 0;
            } catch (\Exception $e) {
                $resultData['error'] = 1;
                $resultData['message'] = __('We can\'t remove the item.');
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
        } else {
            $resultData['error'] = 1;
            $resultData['message'] = __('Something went wrong.');
        }
        $resultData['checkout_data'] = $this->getSerializedCheckoutConfig();
        $this->getResponse()->representJson(
            $this->jsonHelper->jsonEncode($resultData)
        );
    }
}
